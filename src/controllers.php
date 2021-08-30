<?php

use AskNicely\Model\Todo;
use AskNicely\Util\FlashMessage;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

$app['twig'] = $app->share($app->extend('twig', function($twig, $app) {
    $twig->addGlobal('user', $app['session']->get('user'));

    return $twig;
}));


$app->get('/', function () use ($app) {
    return $app['twig']->render('index.html', [
        'readme' => file_get_contents('../README.md'),
    ]);
});


$app->match('/login', function (Request $request) use ($app) {
    $username = $request->get('username');
    $password = $request->get('password');

    if ($username) {
        $sql = "SELECT * FROM users WHERE username = '$username' and password = '$password'";
        $user = $app['db']->fetchAssoc($sql);

        if ($user){
            $app['session']->set('user', $user);
            return $app->redirect('/todo');
        }
    }

    return $app['twig']->render('login.html', array());
});


$app->get('/logout', function () use ($app) {
    $app['session']->set('user', null);
    return $app->redirect('/');
});


$app->get('/todo/{id}', function (Request $request, $id) use ($app) {
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }

    if ($id){
        $todo = Todo::find($id);
        return $app['twig']->render('todo.html', [
            'todo' => $todo,
        ]);
    } else {
        $itemsPerPage = 5;

        // Validate page.  If no page param is provided, then it is defaulted to 1.
        $page = $request->query->get('page');
        if (is_null($page)) {
            $page = 1;
        }
        if (!(is_numeric($page) && $page > 0)) {
            $app['session']->getFlashBag()->add('danger', "Invalid page number!");
            return $app->redirect('/todo');
        }

        // Select a page of results
        $todosResult = Todo::where('user_id', '=', 1)->forPage($page, $itemsPerPage)->get();
        $countResult = Todo::where('user_id', '=', 1)->count();

        // Count all non-paged results.
        $totalPages = ceil($countResult / $itemsPerPage);

        return $app['twig']->render('todos.html', [
            'todos' => $todosResult,
            'current_page' => $page,
            'total_pages' => $totalPages
        ]);
    }
})
->value('id', null);

/**
 * Returns a todo, in JSON format.
 */
$app->get('/todo/{id}/json', function ($id) use ($app) {
    // Check user login.
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }

    $sql = "SELECT * FROM todos WHERE id = '$id'";
    $todo = $app['db']->fetchAssoc($sql);
    $response = new Response(json_encode($todo));
    $response->headers->set('Content-Type', 'application/json');

    return $response;
});


$app->post('/todo/add', function (Request $request) use ($app) {
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }

    // Validate against empty description.
    $user_id = $user['id'];
    $description = $request->get('description');
    if ($description === null || $description === '') {
        $app->abort(500, 'Description cannot be empty.');
    }

    $todo = new Todo([
        'description' => $description,
        'user_id' => $user_id
    ]);
    $todo->save();

    $app['session']->getFlashBag()->add('success', "Successfully added a Todo!");

    return $app->redirect('/todo');
});

/**
 * Change the status of a todo.
 */
$app->post('/todo/toggle/{id}', function (Request $request, $id) use ($app) {
    try {
        // Check user login.
        if (null === $user = $app['session']->get('user')) {
            return $app->redirect('/login');
        }

        $done = $request->get('todo-done');
        if ($done === null) {
            $sqlDone = 0;
        } else if ($done === 'on') {
            $sqlDone = 1;
        } else {
            $app->abort(400, 'There was a problem while changing the status of the todo.');
        }

        $todo = Todo::find($id);
        if ($todo) {
            $todo->done = $sqlDone;
            $todo->save();
        } else {
            throw new Exception("Todo ID#$id doesn't exist");
        }

        $app['session']->getFlashBag()->add('success', "Successfully marked todo #$id as ".($sqlDone === 1 ? "done" : "not done")."!");
    } catch (Exception $e) {
        FlashMessage::danger($app, "Failed to update done status of todo #$id.");
    } finally {
        return $app->redirect('/todo');
    }
});


$app->match('/todo/delete/{id}', function ($id) use ($app) {
    try {
        $todo = Todo::find($id); // throws an unrecovable FatalErrorException in PHP5.
        if ($todo) {
            $todo->delete();
        } else {
            throw new Exception("Todo ID#$id doesn't exist");
        }

        FlashMessage::success($app, "Successfully deleted todo #$id.");
    } catch (Exception $e) {
        FlashMessage::danger($app, "Failed to delete todo #$id.");
    } finally {
        return $app->redirect('/todo');
    }
});