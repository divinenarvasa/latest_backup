<?php

namespace App\Http\Controllers;

use App\Http\Requests\TodoRequest;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\TaskCompletedMail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class TaskController extends Controller
{
    public function index()
    {
        $userId = Auth::id();
    
        $todos = Task::where('user_id', $userId)->where('completed', false)->get();
        Log::info('Incomplete Tasks: ', ['tasks' => $todos->toArray()]);
        
        $completedTodos = Task::where('user_id', $userId)->where('completed', true)->get();
        Log::info('Completed Tasks: ', ['tasks' => $completedTodos->toArray()]);
    
        // Calculate completion rates
        $dailyCompletion = $this->calculateCompletionRate($userId, 'day');
        $weeklyCompletion = $this->calculateCompletionRate($userId, 'week');
        $monthlyCompletion = $this->calculateCompletionRate($userId, 'month');
    
        // Prepare tasks for FullCalendar
        $completedTasks = $completedTodos->map(function($task) {
            return [
                'title' => $task->title,
                'start' => $task->updated_at->toDateString(),
                'description' => $task->description
            ];
        });
    
        return view('Task.index', [
            'todos' => $todos,
            'completedTodos' => $completedTodos,
            'dailyCompletion' => $dailyCompletion,
            'weeklyCompletion' => $weeklyCompletion,
            'monthlyCompletion' => $monthlyCompletion,
            'completedTasks' => $completedTasks
        ]);
    }

    private function calculateCompletionRate($userId, $period)
    {
        $start = match ($period) {
            'day' => now()->startOfDay(),
            'week' => now()->startOfWeek(),
            'month' => now()->startOfMonth(),
            default => now()
        };

        $totalTasks = Task::where('user_id', $userId)->where('created_at', '>=', $start)->count();
        $completedTasks = Task::where('user_id', $userId)
            ->where('completed', true)
            ->where('created_at', '>=', $start)
            ->count();

        return $totalTasks > 0 ? ($completedTasks / $totalTasks) * 100 : 0;
    }

    public function store(TodoRequest $request)
    {
        Task::create([
            'title' => $request->title,
            'description' => $request->description,
            'completed' => false,
            'user_id' => Auth::id()
        ]);
    
        $request->session()->flash('alert-success', 'Task Created Successfully');
        return to_route('tasks.index');
    }

    public function show($id)
    {
        $todo = $this->getUserTaskById($id);
        if (!$todo) {
            session()->flash('error', 'Unable to locate the task');
            return to_route('tasks.index');
        }
        return view('Task.show', compact('todo'));
    }

    public function edit($id)
    {
        $todo = $this->getUserTaskById($id);
        if (!$todo) {
            session()->flash('error', 'Unable to locate the task');
            return to_route('tasks.index');
        }
        return view('Task.edit', compact('todo'));
    }

    public function update(TodoRequest $request)
    {
        $todo = $this->getUserTaskById($request->todo_id);
        if (!$todo) {
            $request->session()->flash('error', 'Unable to locate the task');
            return to_route('tasks.index');
        }

        $todo->update($request->only(['title', 'description', 'completed']));

        $request->session()->flash('alert-info', 'Task Updated Successfully');
        return to_route('tasks.index');
    }

    public function destroy(Request $request)
    {
        $todo = $this->getUserTaskById($request->todo_id);
        if (!$todo) {
            $request->session()->flash('error', 'Unable to locate the task');
            return to_route('tasks.index');
        }

        $todo->delete();
        $request->session()->flash('alert-success', 'Task Deleted Successfully');
        return to_route('tasks.index');
    }

    public function complete($id)
    {
        $userId = Auth::id();
    Log::info('User ID: ' . $userId . ' is completing task with ID: ' . $id);
    $todo = Task::where('id', $id)->where('user_id', $userId)->first();
    
    if (!$todo) {
        session()->flash('error', 'Unable to locate the task');
        return redirect()->route('tasks.index');
    }

    $todo->completed = true;
    $todo->save();

    // Send the email
    try {
        Mail::to(Auth::user()->email)->send(new TaskCompletedMail($todo->title, $todo->description));
        Log::info('Email sent for completed task: ' . $todo->title);
    } catch (\Exception $e) {
        Log::error('Failed to send email: ' . $e->getMessage());
    }

    session()->flash('alert-success', 'Task marked as completed and email sent!');
    return redirect()->route('tasks.completed');
    }

    public function completed()
{
    $userId = Auth::id();
    $completedTodos = Task::where('user_id', $userId)->where('completed', true)->get();
    return view('Task.completed', ['completedTodos' => $completedTodos]);
}
    private function getUserTaskById($id)
    {
        return Task::where('id', $id)->where('user_id', Auth::id())->first();
    }
}
