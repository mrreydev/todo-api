<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

use App\Models\Todo;
use App\Models\Task;

class TodoController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * * Get List Todo
     */
    public function index(Request $request) {
        $todos = Todo::with('tasks')
            ->where('user_id', Auth::user()->id)
            ->paginate(10);

        $response = [
            'status_code' => Response::HTTP_OK,
            'message' => 'Get todos success',
            'data' => $todos
        ];

        return response()->json($response, Response::HTTP_OK);
    }

    /**
     * * Get One Todo
     */
    public function show($id) {
        $todo = Todo::with('tasks')
            ->where('user_id', Auth::user()->id)
            ->where('id', $id)
            ->first();

        if ($todo) {
            $response = [
                'status_code' => Response::HTTP_OK,
                'message' => 'Get todo success',
                'data' => $todo
            ];

            return response()->json($response, Response::HTTP_OK);
        }

        $response = [
            'status_code' => Response::HTTP_NOT_FOUND,
            'message' => 'Todo not found'
        ];

        return response()->json($response, Response::HTTP_NOT_FOUND);
    }

    /**
     * * Create Todo
     */
    public function store(Request $request) {

        $input = $request->all();

        $rules = [
            'name' => 'required|string|min:2',
            'description' => 'string|min:10',
            'due_date' => 'nullable|date',
            'remind_me' => 'required|boolean',
            'important' => 'required|boolean',
            'tasks' => 'array'
        ];

        $validator = Validator::make($input, $rules);

        if ($validator->fails()) {
            $response = [
                'status_code' => Response::HTTP_BAD_REQUEST,
                'errors' => $validator->errors()
            ];

            return response()->json($response, Response::HTTP_BAD_REQUEST);
        }

        $todo = new Todo();
        $todo->user_id = Auth::user()->id;
        $todo->name = $input['name'];
        $todo->description = $input['description'];
        $todo->due_date = $input['due_date'];
        $todo->remind_me = $input['remind_me'];
        $todo->important = $input['important'];

        if ($todo->save()) {
            $dataPivot = [];

            foreach ($input['tasks'] as $key => $value) {
                $row = [
                    'todo_id' => $todo->id,
                    'task' => $value['task'],
                    'finished' => $value['finished']
                ];

                array_push($dataPivot, $row);
            }

            Task::insert($dataPivot);

            $todo->tasks();

            $response = [
                'status_code' => Response::HTTP_CREATED,
                'message' => 'Create Todo Success',
                'data' => $todo
            ];

            return response()->json($response, Response::HTTP_CREATED);
        }

        $response = [
            'message' => 'Create Todos Failed',
            'status_code' => Response::HTTP_INTERNAL_SERVER_ERROR
        ];

        return response()->json($response, Response::HTTP_INTERNAL_SERVER_ERROR);
    }

    /**
     * * Update Todo
     */
    public function update($id, Request $request) {
        $input = $request->all();

        $rules = [
            'name' => 'required|string|min:2',
            'description' => 'string|min:10',
            'due_date' => 'nullable|date',
            'remind_me' => 'required|boolean',
            'important' => 'required|boolean',
            'tasks' => 'array'
        ];

        $validator = Validator::make($input, $rules);

        if ($validator->fails()) {
            $response = [
                'status_code' => Response::HTTP_BAD_REQUEST,
                'errors' => $validator->errors()
            ];

            return response()->json($response, Response::HTTP_BAD_REQUEST);
        }

        $todo = Todo::with('tasks')
            ->where('user_id', Auth::user()->id)
            ->where('id', $id)
            ->first();

        if ($todo) {

            $arrTodo = $todo;
            $arrTodo = $arrTodo->toArray();

            $nullTasks = array_filter($input['tasks'], function ($val) {
                return $val['id'] == null;
            });

            $notNullTasks = array_filter($input['tasks'], function ($val) {
                return $val['id'] != null;
            });

            $deletedTask = array_filter($arrTodo['tasks'], function ($val) use ($notNullTasks) {
                $notNullTasksIds = array_map(function ($valNot){
                    return $valNot['id'];
                }, $notNullTasks);

                return !in_array($val['id'], $notNullTasksIds);
            });

            $updatedTasks = array_filter($notNullTasks, function ($val) use ($deletedTask) {
                $deletedTaskIds = array_map(function ($valNot){
                    return $valNot['id'];
                }, $deletedTask);

                return !in_array($val['id'], $deletedTask);
            });

            // handle delete tasks
            foreach ($deletedTask as $key => $value) {
                $task = Task::find($value['id']);
                if ($task) $task->delete();
            }

            // handle update tasks
            foreach ($updatedTasks as $key => $value) {
                $task = Task::find($value['id']);
                if ($task) {
                    $task->update([
                        'task' => $value['task'],
                        'finished' => $value['finished']
                    ]);
                }
            }

            // handle new tasks
            if ($nullTasks && count($nullTasks)) {
                $dataPivot = array_map(function ($val) use ($todo){
                    return [
                        'todo_id' => $todo->id,
                        'task' => $val['task'],
                        'finished' => $val['finished']
                    ];
                }, $nullTasks);

                Task::insert($dataPivot);
            }

            $todo->name = $input['name'];
            $todo->description = $input['description'];
            $todo->due_date = $input['due_date'];
            $todo->remind_me = $input['remind_me'];
            $todo->important = $input['important'];

            if ($todo->save()) {
                $response = [
                    'status_code' => Response::HTTP_OK,
                    'message' => 'Update todo success'
                ];

                return response()->json($response, Response::HTTP_OK);
            } else {
                $response = [
                    'status_code' => Response::HTTP_INTERNAL_SERVER_ERROR,
                    'message' => 'Update todo failed'
                ];

                return response()->json($response, Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }

        $response = [
            'status_code' => Response::HTTP_NOT_FOUND,
            'message' => 'Todo not found'
        ];

        return response()->json($response, Response::HTTP_NOT_FOUND);
    }

    /**
     * * Delete Todo
     */
    public function delete($id) {
        $todo = Todo::with('tasks')
            ->where('user_id', Auth::user()->id)
            ->where('id', $id)
            ->first();

        if ($todo) {
            $tasks = Task::where('todo_id', $id);

            if ($tasks->delete() && $todo->delete()) {
                $response = [
                    'status_code' => Response::HTTP_OK,
                    'message' => 'Delete todo success'
                ];

                return response()->json($response, Response::HTTP_OK);
            } else {
                $response = [
                    'status_code' => Response::HTTP_INTERNAL_SERVER_ERROR,
                    'message' => 'Delete todo failed'
                ];

                return response()->json($response, Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }

        $response = [
            'status_code' => Response::HTTP_NOT_FOUND,
            'message' => 'Todo not found'
        ];

        return response()->json($response, Response::HTTP_NOT_FOUND);
    }
}
