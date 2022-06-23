require("dotenv").config(); // hide sensitive information to environment variables

const express = require('express');
const http = require('http');
const cors = require('cors');
const app = express();
app.use(express.json());
//allow cross origin requests
// var whitelist = ['http://localhost:3001', 'https://localhost:3000'];
// var corsOptions = {
//   origin: function (origin, callback) {
//     if (whitelist.indexOf(origin) !== -1) {
//       callback(null, true)
//     } else {
//       callback(new Error('Not allowed by CORS'))
//     }
//   }
// }

app.use(cors());
//allow cross origin resource sharing
// app.use(cors(corsOptions));

const userRouter = require("./api/users/user.router");
const healthRouter = require("./api/health/api_health");
const terminalRouter = require("./api/terminal/terminal");

// app.use("/api/users", userRouter);
// app.listen(port, () => {
//     console.log(`Listening to port ${port}`);
// });

app.use("/api/arc", userRouter);
app.use("/api/status", healthRouter);
app.use("/api/terminal/", terminalRouter);

http.createServer(app).listen(3000, () => {
  console.log("Listening to port 3000");
});


