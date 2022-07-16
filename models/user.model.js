const mongoose = require('mongoose');
const bcrypt = require('bcrypt');
const {roles} = require('../utils/constants');
const UserSchema = new mongoose.Schema({
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
    role:{
        type:String,
        enum: [roles.admin, roles.moderator, roles.client],
        default: roles.client
    }
})
// pre saving user
UserSchema.pre('save', async function (next) { //cant use arrow function here (becoz of ||this||)
    try {
        if (this.isNew) { //isNew is a property in mongoose which tells if the document is new or not because evertime we hit the user.save() method it will hasshed the password again
            const salt = await bcrypt.genSalt(10);
            const hashedPassword = await bcrypt.hash(this.password, salt)
            this.password = hashedPassword; //overwriting the password with hased password
            if(this.email === process.env.ADMIN_EMAIL.toLowerCase()){
                this.role = roles.admin
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
        return await bcrypt.compare(password, this.password);
    } catch (error) {
        throw creteHttpError.InternalServerError(error.message);
    }
}
module.exports = mongoose.model('user', UserSchema);
