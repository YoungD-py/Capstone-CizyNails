<!DOCTYPE html>
<html>
<head>
    <title>Debug - Users</title>
    <style>
        body { font-family: Arial; margin: 20px; background-color: #f5f5f5; }
        .container { max-width: 1000px; margin: 0 auto; background: white; padding: 20px; border-radius: 5px; }
        table { border-collapse: collapse; width: 100%; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 12px; text-align: left; }
        th { background-color: #4CAF50; color: white; }
        tr:nth-child(even) { background-color: #f9f9f9; }
        .null-value { color: red; font-weight: bold; }
        .has-value { color: green; }
        .buttons { margin: 20px 0; }
        a, button { display: inline-block; padding: 10px 20px; margin: 5px; background-color: #008CBA; color: white; text-decoration: none; border: none; border-radius: 4px; cursor: pointer; }
        a:hover, button:hover { background-color: #007399; }
        .message { padding: 10px; background-color: #d4edda; border: 1px solid #c3e6cb; color: #155724; border-radius: 4px; margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Users Table Debug</h1>
        
        @if(isset($message))
            <div class="message">{{ $message }}</div>
        @endif
        
        <div class="buttons">
            <a href="/debug/update-phones">Update NULL Phones</a>
            <a href="/debug/fix-all-phones">Fix ALL Phones</a>
            <a href="/debug/users">Refresh</a>
        </div>
        
        <h3>Columns in users table:</h3>
        <p>{{ implode(', ', $columnNames ?? []) }}</p>
        
        <table>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Role</th>
            </tr>
            @forelse($users as $user)
            <tr>
                <td>{{ $user->id }}</td>
                <td>{{ $user->name }}</td>
                <td>{{ $user->email }}</td>
                <td class="{{ $user->phone ? 'has-value' : 'null-value' }}">{{ $user->phone ?? 'NULL' }}</td>
                <td>{{ $user->role }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="5">No users found</td>
            </tr>
            @endforelse
        </table>
    </div>
</body>
</html>
