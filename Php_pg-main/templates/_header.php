<?php
require_once __DIR__ . '/../config.php';
?><!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Proposal Generator (PHP)</title>
  <style>
    body { font-family: Arial, sans-serif; margin: 2rem; }
    .container { max-width: 960px; margin: 0 auto; }
    a { text-decoration: none; }
    .btn { display: inline-block; padding: 8px 12px; border: 1px solid #333; margin: 4px 0; }
    .table { width: 100%; border-collapse: collapse; }
    .table th, .table td { border: 1px solid #ddd; padding: 8px; }
    .muted { color: #666; }
    textarea { width: 100%; min-height: 100px; }
    input[type=text], input[type=date], input[type=email], input[type=tel] { width: 100%; padding: 6px; }
    .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
    .grid-3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem; }
  </style>
  <link rel="stylesheet" href="/assets/app.css">
</head>
<body><div class="container">
  <h1>Proposal Generator (PHP)</h1>
  <nav><a class="btn" href="index.php">All Proposals</a> <a class="btn" href="index.php?action=new">New Proposal</a></nav>
  <hr>
