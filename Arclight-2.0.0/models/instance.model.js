const mongoose = require('mongoose');

const InstanceSchema = new mongoose.Schema({
    userid: {
        type: String,
        required: true
    },
    uuid: {
        type: String
    },
    action: {
        type: String,
        required: true
    },
    username: {
        type: String,
        required: true
    },
    instance_type: {
        type: String,
        required: true,
        enum: ['vm', 'bare_metal']
    },
    domain_name: {
        type: String,
        required: true,
        unique: true
    },
    os: {
        type: String,
    },
    vcpu: {
        type: Number,
    },
    cores: {
        type: Number,
    },
    threads: {
        type: Number,
    },
    memory: {
        type: Number,
    },
    memory_unit: {
        type: String,
    },
    source_file_volume: {
        type: String,
    },
    volume_image_name: {
        type: String,
    },
    volume_size: {
        type: Number,
    },
    drive_type: {
        type: String,
    },
    target_bus: {
        type: String,
    },
    storage_pool: {
        type: String,
    },
    existing_driver_type: {
        type: String,
    },
    existing_target_bus: {
        type: String,
    },
    source_file_cd: {
        type: String,
    },
    mac_address: {
        type: String,
        unique: true
    },
    model_type: {
        type: String,
    },
    source_network: {
        type: String,
    }
} , { timestamps: true });
const Instance = mongoose.model('arclight_vm', InstanceSchema);

module.exports = { Instance };
