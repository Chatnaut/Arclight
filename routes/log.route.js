const router = require("express").Router();
const fs = require('fs');
let vncLogs;
let terminalLogs;
//send all logs from two files ../logs/novnc.log, terminal.log to the client asynchronously in an array

router.get('/getlogs', async (req, res, next) => {
    try {
        const path = ['/var/www/html/arclight/logs/novnc.log', '/var/www/html/arclight/logs/terminal.log'];
        //if path exists, send the logs to the client
        await fs.access(path[0], (err) => {
            if (err) {
                console.log(err);
                return;
            }
            fs.readFile(path[0],(err, data) => {
                if (err) {
                    vncLogs = `${err}`;
                } else {
                    vncLogs = data.toString();
                }
            }
            );
        }
        );

        await fs.access(path[1], (err) => {
            if (err) {
                console.log(err);
                return;
            }
            fs.readFile(path[1], (err, data) => {
                if (err) {
                    terminalLogs = `${err}`;
                }
                else {
                    terminalLogs = data.toString();
                }
            }
            );
        });


res.status(200).json({
    success: 1,
    message: "Logs retrieved successfully",
    result: [vncLogs, terminalLogs]
});
    } catch (error) {
    res.status(500).json({
        success: 0,
        message: "Error retrieving logs",
        result: error
    });
}
}
)

module.exports = router;
