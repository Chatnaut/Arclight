const mongoose = require('mongoose');
const bcrypt = require('bcrypt');
const { roles } = require('../utils/constants');
const UserSchema = new mongoose.Schema({
    username: {
        type: String,
        required: true,
    },
    email: {
        type: String,
        required: true,
        lowercase: true,
        unique: true
    },
    password: {
        type: String,
        required: true
    },
    role: {
        type: String,
        enum: [roles.admin, roles.moderator, roles.client],
        default: roles.client
    },
    status: {
        type: String,
        enum: ['active', 'inactive'],
        default: 'inactive'
    }
}, { timestamps: true });
// pre saving user
UserSchema.pre('save', async function (next) { //cant use arrow function here (becoz of ||this||)
    try {
        if (this.isNew) { //isNew is a property in mongoose which tells if the document is new or not because evertime we hit the user.save() method it will hasshed the password again
            const salt = await bcrypt.genSalt(10);
            const hashedPassword = await bcrypt.hash(this.password, salt)
            this.password = hashedPassword; //overwriting the password with hased password
            if (this.email === process.env.ADMIN_EMAIL.toLowerCase()) {
                this.role = roles.enterprise;
                this.status = 'active';
            }
        }
        next();
    } catch (err) {
        next(err)
    }
});
//Method for faster compare password used in passport.auth
UserSchema.methods.isvalidPassword = async function (password) {
    try {
        //php hash generates $2y$ while node generates the $2b$ so we need to replace $2b$ with $2y$
        const hashedPassword = this.password.replace("$2y$", "$2b$");
        return await bcrypt.compare(password, hashedPassword);
    } catch (error) {
        throw creteHttpError.InternalServerError(error.message);
    }
}
const User = mongoose.model('arclight_user', UserSchema);

//arclight_config schema------------------------------------------------------
const ArclightConfigSchema = new mongoose.Schema({
    name: {
        type: String,
        required: true
    },
    value: {
        type: String,
    },
    userid: {
        type: String,
        required: true
    }
}, { timestamps: true });
const ArclightConfig = mongoose.model('arclight_config', ArclightConfigSchema);

//arclight_log schema------------------------------------------------------
const ArclightLogSchema = new mongoose.Schema({
    userid: {
        type: String,
        required: true
    },
    description: {
        type: String,
    },
    host_uuid: {
        type: String,
    },
    domain_uuid: {
        type: String,
    }
}, { timestamps: true });
const ArclightLog = mongoose.model('arclight_event', ArclightLogSchema);

module.exports = {
    User,
    ArclightConfig,
    ArclightLog
}




