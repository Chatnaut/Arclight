const pool = require("../../config/database");
module.exports = {
  //CREATE
  create: (data, callback) => {
    pool.query(
      `INSERT into registration(firstName,lastName,gender,email,password,number) values(?,?,?,?,?,?)`,
      [
        data.first_name,
        data.last_name,
        data.gender,
        data.email,
        data.password,
        data.number,
      ],
      (error, results, fields) => {
        if (error) {
          callback(error);
        }
        return callback(null, results);
      }
    );
  },
  //Create Instance
  createInstance: (data, callback) => {
    pool.query(
      //24
      `INSERT INTO arclight_vm (userid, uuid, action, username, instance_type, domain_name, os, vcpu, cores, threads, memory, memory_unit, source_file_volume, volume_image_name, volume_size, driver_type, target_bus, storage_pool, existing_driver_type, existing_target_bus, source_file_cd, mac_address, model_type, source_network, dt) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)`,
      [
        data.userid,
        data.uuid,
        data.action,
        data.username,
        data.instance_type,
        data.domain_name,
        data.os,
        data.vcpu,
        data.cores,
        data.threads,
        data.memory,
        data.memory_unit,
        data.source_file_volume,
        data.volume_image_name,
        data.volume_size,
        data.driver_type,
        data.target_bus,
        data.storage_pool,
        data.existing_driver_type,
        data.existing_target_bus,
        data.source_file_cd,
        data.mac_address,
        data.model_type,
        data.source_network,
        data.dt,
      ],
      (error, results, fields) => {
        if (error) {
          callback(error);
        }
        return callback(null, results);
      }
    );
  },
      //dynamically update arclight_vm table columns with only requested fields
      updateUserInstance: (data, callback) => {
          let query = `UPDATE arclight_vm SET `;
          let queryValues = [];
          for (let key in data) {
            if (key !== "domain_name") {
              query += `${key} = ?, `; //output will be like  'instance_type = ?, domain_name = ?
              queryValues.push(data[key]); //output will be like  ['instance_type', 'domain_name']
            }
          }
          query = query.slice(0, -2);
          query += ` WHERE domain_name = ?`;
          queryValues.push(data.domain_name);
          pool.query(query, queryValues, (error, results, fields) => {
            if (error) {
              callback(error);
            }
            return callback(null, results);
          }
          );
        },
          
  //GET
  getUsers: (callback) => {
    pool.query(
      `SELECT id,firstName,lastName,gender,email,password,number FROM registration`,
      [],
      (error, results, fields) => {
        if (error) {
          callback(error);
        }
        return callback(null, results);
      }
    );
  },
  getInstance: (id, callback) => {
    pool.query(
      `SELECT * from arclight_vm WHERE userid = ?`,
      [id],
      (error, results, fields) => {
        if (error) {
          callback(error);
        }
        return callback(null, results);
      }
    );
  },
  getarcUsers: (callback) => {
    pool.query(
      `SELECT userid, username, email, password,roles FROM arclight_users`,
      [],
      (error, results, fields) => {
        if (error) {
          callback(error);
        }
        return callback(null, results);
      }
    );
  },
  //UPDATE
  updateUser: (data, callback) => {
    pool.query(
      `UPDATE registration set firstName=?,lastName=?,gender=?,email=?,password=?,number=? WHERE id = ?`,
      [
        data.first_name,
        data.last_name,
        data.gender,
        data.email,
        data.password,
        data.number,
        data.id,
      ],
      (error, results, fields) => {
        if (error) {
          callback(error);
        }
        return callback(null, results);
      }
    );
  },

  //DELETE
  deleteUser: (data, callback) => {
    pool.query(
      `DELETE FROM registration WHERE id = ?`,
      [data.id],
      (error, results, fields) => {
        if (error) {
          callback(error);
        }
        return callback(null, results);
      }
    );
  },

  //DELETE Instance
  deleteUserInstance: (data, callback) => {
    pool.query(
      `DELETE FROM arclight_vm WHERE userid = ? AND domain_name = ?`,
      [data.userid, data.domain_name],
      (error, results, fields) => {
        if (error) {
          callback(error);
        }
        return callback(null, results);
      }
    );
  },
  //Authorize users using JWT
  getUserByUserEmail: (email, callback) => {
    pool.query(
      `SELECT * from arclight_users WHERE email = ?`,
      [email],
      (error, results, fields) => {
        if (error) {
          callback(error);
        }
        return callback(null, results[0]);
      }
    );
  },

  //get arclight users and config inner join
  getAUC: (email, callback) => {
    pool.query(
      `SELECT * from arclight_users INNER JOIN arclight_config ON arclight_users.userid = arclight_config.userid WHERE email = ?`,
      [email],
      (error, results, fields) => {
        if (error) {
          callback(error);
        }
        return callback(null, results);
      }
    );
  },

  
};
