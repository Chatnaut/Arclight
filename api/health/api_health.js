const router = require("express").Router(); // create a router instance

router.get('/health', async(req, res) => {
    const data = {
        uptime: process.uptime(),
        message: 'Online',
        date: new Date(),
        version: process.version,
        platform: process.platform,
        arch: process.arch,
        memory: process.memoryUsage(),
        cpu: process.cpuUsage(),
        //current
        port: process.env.PORT,
        status: 200
    };
    try{
        res.status(200).send(data);
    }catch(error){
        data.message = "Offline";
        res.status(503).send();
    }
    
});

module.exports = router;