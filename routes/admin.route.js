const { User } = require('../models/user.model')
const router = require('express').Router();
const mongoose = require('mongoose');
const { roles } = require('../utils/constants')
router.get('/users', async (req, res, next) => {
    try {
        const users = await User.find();
        res.render('manage-users', { users })
    } catch (error) {
        next(error)
    }
})

// update roles
router.post('/update-role', async (req, res, next) => {
    const { id, role } = req.body;
    if (!id || !role) {
        req.flash('error', 'Invalid Request');
        return res.redirect('back')
    }
    //check for valid mongoose object id
    if (!mongoose.Types.ObjectId.isValid(id)) {
        req.flash('error', 'Invalid Id');
        return res.redirect('back')
    }
    //check for valid roles
    const rolesArray = Object.values(roles);
    if (!rolesArray.includes(role)) {
        req.flash('error', 'Invalid Role');
        return res.redirect('back')
    }
    //admins restriction to remove itself
    // if (req.user.id === id) {
    //     req.flash('error', 'Admin cannot be removed or change their role, ask another admin to change role for this admin.');
    //     // return res.redirect('back')
    // }
    //finally update user
    const user = await User.findByIdAndUpdate(id, { role: role }, { new: true, runValidators: true })
    req.flash('info', `Role updated for ${user.email} to ${user.role}`)
    res.redirect('back')
})

//update status
router.post('/update-status', async (req, res, next) => {
    const { id, status } = req.body;
    if (!id || !status) {
        req.flash('error', 'Invalid Request');
        return res.redirect('back');
    }
    //check for valid mongoose object id
    if (!mongoose.Types.ObjectId.isValid(id)) {
        req.flash('error', 'Invalid Id');
        return res.redirect('back');
    }
    //finally update user
    if (status === 'active') {
        const user = await User.findByIdAndUpdate(id, { status: 'inactive' }, { new: true, runValidators: true });
        req.flash('info', `Status updated for ${user.email} to ${user.status}`);
        res.status(200).json({
            success: 1,
            message: `Status updated for ${user.email} to ${user.status}`,
            result: user.status
        });
    } else {
        const user = await User.findByIdAndUpdate(id, { status: 'active' }, { new: true, runValidators: true });
        req.flash('info', `Status updated for ${user.email} to ${user.status}`);
        res.status(200).json({
            success: 1,
            message: `Status updated for ${user.email} to ${user.status}`,
            result: user.status
        });
    }
});

module.exports = router