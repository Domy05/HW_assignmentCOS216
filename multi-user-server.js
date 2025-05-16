const http = require('http');
const { Server } = require('socket.io');

// Create HTTP server
const server = http.createServer();

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
    io.emit('chat message', { id: socket.id, message: msg });
  });

  // Notify when a user disconnects
  socket.on('disconnect', () => {
    console.log('User disconnected:', socket.id);
  });
});

// Start the server
server.listen(3000, () => {
  console.log('Socket.IO server running at http://localhost:3000/');
});