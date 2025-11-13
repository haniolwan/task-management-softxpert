<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ListTaskRequest;
use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Models\Task;

class TaskController extends Controller
{
    public function index(ListTaskRequest $request)
    {
        $query = Task::query();

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('due_date')) {
            $query->whereDate('due_date', $request->input('due_date'));
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

        return response()->json([
            'message' => 'Tasks retrieved successfully',
            'data' => $tasks->items()
        ], 200);
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

        return response()->json([
            'message' => 'Task successfully created',
            'task' => $task->load('dependencies'),
        ], 201);
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


    public function update(UpdateTaskRequest $request, Task $task)
    {
        $validated = $request->validated();
        $authUser = auth()->user();
        $task->load('dependencies');

        if ($authUser->can('update tasks')) {

            $fields = array_filter($validated, fn($v) => !is_null($v) && $v !== []);
            unset($fields['dependency_ids']);
            $task->update($fields);


            $dependencyIds = $validated['dependency_ids'] ?? [];

            if (!empty($dependencyIds)) {
                $valid_task_ids = [];
                foreach ($dependencyIds as $id) {
                    $id = (int) $id;

                    if (!$this->canAddDependency($task, $id)) {
                        return response()->json([
                            'message' => "Cannot add task #{$id} due to circular dependency."
                        ], 400);
                    }

                    $valid_task_ids[] = $id;
                }
                $task->dependencies()->sync($valid_task_ids);
            }
        } elseif ($authUser->can('update task status')) {
            $status = $validated['status'] ?? null;
            if ($status === 'completed') {

                foreach ($task->dependencies as $dependency) {
                    if ($dependency->status !== 'completed') {
                        return response()->json([
                            'message' => "Cannot set task '{$task->title}' to completed because dependency '{$dependency->title}' is not completed yet."
                        ], 400);
                    }
                }
            }

            $task->update(['status' => $validated['status'] ?? $task->status]);
        } else {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json([
            'message' => 'Task successfully updated',
            'task' => $task->load('dependencies'),
        ], 200);
    }

}
