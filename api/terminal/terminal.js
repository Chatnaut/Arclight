const router = require('express').Router();
const { exec, spawn } = require("child_process");

router.post('/wssh', (req, res) => {
    const body = req.body;
    //using the spawn method to read output continuously, and without limit (future-use):
    const child = spawn('wssh', [`${body.cert_option}`, `${body.key_option}`, `--log-file-prefix=/var/www/html/arclight/logs/terminal.log`]);

    child.stdout.on('data', (data) => {
        console.log(`stdout: ${data}`);
        return res.status(500).json({
            success: 0,
            data: data
        });
    })

    child.stderr.on('data', (data) => {
        console.log(`stderr: ${data}`);
        return res.status(200).json({
            success: 1,
            data: data
        });
    });
    child.on('error', (error) => {
        console.log(`error: ${error}`);
        return res.status(500).json({
            success: 0,
            message: "SSH connection error",
            error: error
        });
    });
    child.on('exit', (code, signal) => {
        if (code) {
            console.log(`child process exited with code ${code}`);
        }
        if (signal) {
            console.log(`child process exited with signal ${signal}`);
        }
    });

});
module.exports = router;