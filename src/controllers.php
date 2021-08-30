<?php

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


$app->get('/todo/{id}', function ($id) use ($app) {
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }

    if ($id){
        $sql = "SELECT * FROM todos WHERE id = '$id'";
        $todo = $app['db']->fetchAssoc($sql);

        return $app['twig']->render('todo.html', [
            'todo' => $todo,
        ]);
    } else {
        $sql = "SELECT * FROM todos WHERE user_id = '${user['id']}'";
        $todos = $app['db']->fetchAll($sql);

        return $app['twig']->render('todos.html', [
            'todos' => $todos,
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

    $user_id = $user['id'];
    $description = $request->get('description');
    if ($description === null || $description === '') {
        $app->abort(500, 'Description cannot be empty.');
    }

    $sql = "INSERT INTO todos (user_id, description) VALUES ('$user_id', '$description')";
    $app['db']->executeUpdate($sql);

    $app['session']->getFlashBag()->add('success', "Successfully added a Todo!");

    return $app->redirect('/todo');
});

/**
 * Change the status of a todo.
 */
$app->post('/todo/toggle/{id}', function (Request $request, $id) use ($app) {
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

    $sql = "UPDATE todos SET done = '$sqlDone' WHERE id = $id LIMIT 1;";
    $app['db']->executeUpdate($sql);

    $app['session']->getFlashBag()->add('success', "Successfully marked todo #$id as ".($sqlDone === 1 ? "done" : "not done")."!");

    return $app->redirect('/todo');
});


$app->match('/todo/delete/{id}', function ($id) use ($app) {

    $sql = "DELETE FROM todos WHERE id = '$id'";
    $app['db']->executeUpdate($sql);

    $app['session']->getFlashBag()->add('success', "Successfully deleted todo #$id!");

    return $app->redirect('/todo');
});