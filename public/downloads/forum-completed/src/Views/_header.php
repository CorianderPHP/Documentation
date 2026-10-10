<?php
use CorianderCore\Core\Support\PublicUrl;

// The downloaded demo has no local documentation router; use the official guide.
$forumGuideReturn = 'https://corianderphp.com' . ($forumGuideReturn ?? '/guided-projects/forum');
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $title ?? 'Forum Demo' ?></title>
    <link rel="stylesheet" href="<?= PublicUrl::versionedAsset('assets/css/output.css') ?>">
</head>
<body class="bg-white text-black font-poppins">
<nav class="mx-auto flex max-w-screen-xl flex-wrap gap-6 px-6 py-4">
    <a href="/forum-demo">Forum</a>
    <a href="/forum-demo/topics">Topics</a>
    <a href="/forum-demo/login">Demo accounts</a>
    <?php if ($permissions['admin.view'] ?? false): ?>
        <a href="/forum-demo/admin">Admin</a>
    <?php endif; ?>
</nav>
<main class="mx-auto max-w-screen-xl">
