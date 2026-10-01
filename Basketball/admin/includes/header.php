<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Basketball League</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #0033a0;
            --secondary: #d9272d;
            --accent: #ffc800;
        }
        body {
            font-family: 'Montserrat', sans-serif;
            background-color: #f8fafc;
        }
        .sidebar {
            background: linear-gradient(180deg, var(--primary), #001c5b);
            height: 100vh; /* Changed from h-screen to 100vh for better compatibility */
            position: sticky;
            top: 0;
            overflow-y: auto;
        }
        .nav-item.active {
            background: rgba(255,255,255,0.1);
            border-left: 4px solid var(--accent);
        }
        .stat-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }
    </style>

    <style>
        @media print {
        aside, .print-hidden, .sidebar, .bottom-buttons {
            display: none !important;
        }
        body {
            background: white !important;
            color: black !important;
            margin: 0;
            padding: 0;
        }
        .max-w-7xl {
            max-width: 100% !important;
            margin: 0 !important;
            padding: 0 1rem !important;
        }
        header {
            background: white !important;
            color: black !important;
            text-align: center;
            border-bottom: 2px solid black;
            padding: 10px 0;
        }
        table, th, td {
            border: 1px solid #000 !important;
            border-collapse: collapse !important;
        }
        .shadow, .shadow-inner, .rounded, .rounded-lg {
            box-shadow: none !important;
            border-radius: 0 !important;
        }
        @page {
            size: A4 portrait;
            margin: 10mm;
        }
        table {
            page-break-inside: avoid;
        }
        body, table, input, label, h1, h2, h3, h4, p {
            font-size: 13px !important;
        }
        * {
            overflow: visible !important;
        }
        }
        </style>

</head>
<body class="flex h-screen">
