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
        $tasks = $query->paginate($page);

        return response()->json([
            'message' => 'Tasks retrieved successfully',
            'data' => $tasks->items(),
            'pagination' => [
                'total' => $tasks->total(),
                'per_page' => $tasks->perPage(),
                'current_page' => $tasks->currentPage(),
                'last_page' => $tasks->lastPage(),
            ]
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

        return response()->json([
            'message' => 'Task successfully created',
            'task' => $task,
        ], 201);
    }

    public function update(UpdateTaskRequest $request, Task $task)
    {
        $validated = $request->validated();

        if (!auth()->user()->hasRole('manager')) {
            $validated = ['status' => $validated['status'] ?? $task->status];

            if ($task->assignee_id !== auth()->id()) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }
        }

        $task->update(array_filter($validated, fn($value) => !is_null($value)));

        return response()->json([
            'message' => 'Task successfully updated',
            'task' => $task->refresh(),
        ], 200);
    }
}
