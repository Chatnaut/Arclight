//arclight config route
const {ArclightConfig} = require('../models/user.model');
const router = require('express').Router();
const mongoose = require('mongoose');

router.post('/arc_config', async (req, res, next) => {
    try {
        const { name, value, userid } = req.body;
        if (!mongoose.Types.ObjectId.isValid(userid)) {
            res.status(400).json({
                success: 0,
                message: "Invalid User Id"
            });
        }

        if (name === 'cert_path') {
            const data = await ArclightConfig.updateMany({ name: name}, { $set: { value: value } }, { new: true, upsert: true });
            res.status(200).json({
                success: 1,
                message: "Certificate path updated successfully",
                result: data
            });
        }
        //if name is key_path find and update value where userid = req.user.id
        if (name === 'key_path') {
            const data = await ArclightConfig.updateMany({ name: name}, { $set: { value: value } }, { new: true, upsert: true });
            res.status(200).json({
                success: 1,
                message: "Key path updated successfully",
                result: data
            });
        }
        //if name is theme_color find and update value where userid = req.user.id
        if (name === 'theme_color') {
            const data = await ArclightConfig.findOneAndUpdate({ name: name, userid: userid }, { $set: { value: value } }, { new: true, upsert: true });
            res.status(200).json({
                success: 1,
                message: "Theme color updated successfully",
                result: data
            });
        }
    } catch (error) {
        res.status(500).json({
            success: 0,
            message: "Internal server error",
            error: error.message
        });
    }
}
);

//get all arclight configs for user
router.get('/arc_config/:userid', async (req, res, next) => {
    try {
        const { userid } = req.params;
        if (!mongoose.Types.ObjectId.isValid(userid)) {
            res.status(400).json({
                success: 0,
                message: "Invalid User Id"
            });
        }
        const data = await ArclightConfig.find({ userid: userid });
        const certs = await ArclightConfig.find({ name: 'cert_path' });
        const keys = await ArclightConfig.find({ name: 'key_path' });
        res.status(200).json({
            success: 1,
            message: "Arclight configs fetched successfully",
            result: data,
            certs: certs.length > 0 ? certs[0].value : '',
            keys: keys.length > 0 ? keys[0].value : ''
        });
    } catch (error) {
        res.status(500).json({
            success: 0,
            message: "Internal server error",
            error: error
        });
    }
}
);

module.exports = router;
