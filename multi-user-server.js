const http = require('http');
const { Server } = require('socket.io');
const readline = require('readline'); // Get port from command-line argument or prompt

function isValidPort(port) {
  return Number.isInteger(port) && port >= 1024 && port <= 49151;
}

function startServer(port) {
    const server = http.createServer(); // Create HTTP server

    // Attach socket.io to the server
    const io = new Server(server, {
        cors: {
            origin: "*", // Allow all origins
            methods: ["GET", "POST"]
        }
    });

    // Listen for client connections
    io.on('connection', (socket) => {
        console.log('A user connected:', socket.id);

        // Broadcast when a user sends a message
        socket.on('chat message', (msg) => {
            // Send the message to all connected clients
            io.emit('chat message', { 
                id: socket.id, 
                message: msg 
            });
        });

        // Notify when a user disconnects
        socket.on('disconnect', () => {
            console.log('User disconnected:', socket.id);
        });
    });

    // Start the server
    server.listen(port, () => {
        console.log(`Socket.IO server running at http://localhost:${port}/`);
    });
}

// Try to get port from command-line argument
const argPort = parseInt(process.argv[2], 10);
if (isValidPort(argPort)) {
  startServer(argPort);
} else {
  // Prompt user for port
  const rl = readline.createInterface({
    input: process.stdin,
    output: process.stdout
  });

  function askPort() {
    rl.question('Enter a port number (1024-49151): ', (answer) => {
      const port = parseInt(answer, 10);
      if (isValidPort(port)) {
        rl.close();
        startServer(port);
      } else {
        console.log('Invalid port. Please use a port between 1024 and 49151.');
        askPort();
      }
    });
  }
  askPort();
}