<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ListTaskRequest;
use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Http\Traits\ApiResponserTrait;
use App\Models\Task;

class TaskController extends Controller
{
    use ApiResponserTrait;
    public function index(ListTaskRequest $request)
    {
        $query = Task::query();

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('from') || $request->filled('to')) {
            $query->whereBetween('due_date', [
                $request->input('from', '1970-01-01'),
                $request->input('to', '2100-01-01')
            ]);
        }

        if (!auth()->user()->hasRole('manager')) {
            $query->where('assignee_id', auth()->id());
        } else {
            if ($request->filled('assignee_id')) {
                $query->where('assignee_id', (int) $request->input('assignee_id'));
            }
        }

        $page = (int) $request->input('per_page', 10);

        $tasks = $query->with(['dependencies'])->paginate($page);

        return $this->success([
            'items' => $tasks->items(),
            'pagination' => [
                'total' => $tasks->total(),
                'per_page' => $tasks->perPage(),
                'current_page' => $tasks->currentPage(),
                'last_page' => $tasks->lastPage(),
                'from' => $tasks->firstItem(),
                'to' => $tasks->lastItem(),
            ],
        ], 'Tasks retrieved successfully');
    }


    public function show(Task $task)
    {
        $user = auth()->user();

        if (!$user->hasRole('manager') && $task->assignee_id != $user->id) {
            return $this->error('Forbidden', [], 403);
        }

        return $this->success(
            $task->load('dependencies'),
            'Task retrieved successfully'
        );
    }

    public function store(StoreTaskRequest $request)
    {
        $request->validated();
        $task = Task::create([
            'title' => $request->title,
            'description' => $request->description,
            'assignee_id' => $request->assignee_id,
            'due_date' => $request->due_date,
            'status' => $request->status ?? 'pending'
        ]);

        if (!empty($request->dependency_ids)) {
            $task->dependencies()->syncWithoutDetaching($request->dependency_ids);
        }

        return $this->success($task->load('dependencies'), 'Task successfully created', 201);
    }

    private function canAddDependency(Task $task, int $taskId): bool
    {
        $new_dependency = Task::find($taskId);
        if (!$new_dependency) {
            return false;
        }

        $all_visited_children = $this->getAllDependencies($new_dependency);

        return !in_array($task->id, $all_visited_children);
    }

    private function getAllDependencies(Task $task, &$visited = [])
    {
        foreach ($task->dependencies as $dependency) {
            if ($dependency && !in_array($dependency->id, $visited)) {
                $visited[] = $dependency->id;
                $this->getAllDependencies($dependency, $visited);
            }
        }
        return $visited;
    }

    public function updateStatus(UpdateTaskRequest $request, Task $task)
    {
        $validated = $request->validated();
        $status = $validated['status'] ?? null;
        if ($status === 'completed') {
            foreach ($task->dependencies as $dependency) {
                if ($dependency->status !== 'completed') {
                    $this->error('Cannot set task' . $task->title . ' to completed because dependency ' . $dependency->title . ' is not completed yet.');
                }
            }
        }

        $task->update(['status' => $validated['status'] ?? $task->status]);

        return $this->success($task->load('dependencies'), 'Task status successfully updated');
    }

    public function update(UpdateTaskRequest $request, Task $task)
    {
        $validated = $request->validated();
        $task->load('dependencies');

        $fields = array_filter($validated, fn($v) => !is_null($v) && $v !== []);
        unset($fields['dependency_ids']);
        $task->update($fields);

        $dependencyIds = $validated['dependency_ids'] ?? [];

        if (!empty($dependencyIds)) {
            $valid_task_ids = [];
            foreach ($dependencyIds as $id) {
                $id = (int) $id;

                if (!$this->canAddDependency($task, $id)) {
                    $this->error($task->title . ' already dependant on selected task');
                }

                $valid_task_ids[] = $id;
            }
            $task->dependencies()->sync($valid_task_ids);
        }

        return $this->success($task->load('dependencies'), 'Task successfully updated');
    }
}

