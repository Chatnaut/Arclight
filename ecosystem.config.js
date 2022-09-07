module.exports = {
    apps: [{
        name: "arc",
        script: "./app.js",
        watch: false,
        log_date_format: 'YYYY-MM-DD HH:mm:ss SSS',
        autorestart: true,
	env: {
            PORT: '3000',
        }
    }]
}
