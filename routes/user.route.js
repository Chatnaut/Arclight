const {User} = require('../models/user.model')
const router = require('express').Router();
const mongoose = require('mongoose');

//get all users
router.get('/arcuser', async (req, res) => {
    try {
        const users = await User.find();
        res.status(200).json({
            success: 1,
            message: 'Users fetched successfully',
            result: users
        });
    } catch (error) {
        res.status(500).json({
            success: 0,
            message: 'Internal server error',
            error: error.message
        });
    }
}
);

//get user by id
router.get('/arcuser/:id', async (req, res, next) => {
    try {
        const { id } = req.params;
        if (!mongoose.Types.ObjectId.isValid(id)) {
            // req.flash('error', 'Invalid Id');
            // res.redirect('/admin/users');
            res.status(400).json({
                success: 0,
                message: "Invalid User Id"
            });
        }
        const data = await User.findById(id);
        res.status(200).json({
            success: 1,
            message: "Profile retrieved successfully",
            result: data
        });
    } catch (error) {
        next(error)
    }
})

module.exports = router;