const{ArclightLog} = require('../models/user.model');
const router = require('express').Router();
const mongoose = require('mongoose');

router.post('/arc_event', async (req, res, next) => {
    try {
        const { userid, description, host_uuid, domain_uuid } = req.body;
        if (!mongoose.Types.ObjectId.isValid(userid)) {
            res.status(400).json({
                success: 0,
                message: "Invalid User Id"
            });
        }
        const data = await ArclightLog.create({ userid: userid, description: description, host_uuid: host_uuid, domain_uuid: domain_uuid });
        res.status(200).json({
            success: 1,
            message: "Event created successfully",
            result: data
        });
    } catch (error) {
        res.status(500).json({
            success: 0,
            message: "Internal server error",
            error: error.message
        });
    }
});

module.exports = router;