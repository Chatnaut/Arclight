"use strict";
var __importDefault = (this && this.__importDefault) || function (mod) {
    return (mod && mod.__esModule) ? mod : { "default": mod };
};
Object.defineProperty(exports, "__esModule", { value: true });
exports.createStoreHelper = exports.makeDataNoCookie = exports.makeData = exports.makeCookie = void 0;
// eslint-disable-next-line eslint-comments/disable-enable-pair
/* eslint-disable @typescript-eslint/explicit-module-boundary-types */
const util_1 = __importDefault(require("util"));
const express_session_1 = __importDefault(require("express-session"));
const MongoStore_1 = __importDefault(require("../lib/MongoStore"));
// Create a connect cookie instance
const makeCookie = () => {
    const cookie = new express_session_1.default.Cookie();
    cookie.maxAge = 10000; // This sets cookie.expire through a setter
    cookie.secure = true;
    cookie.domain = 'cow.com';
    cookie.sameSite = false;
    return cookie;
};
exports.makeCookie = makeCookie;
// Create session data
const makeData = () => {
    return {
        foo: 'bar',
        baz: {
            cow: 'moo',
            chicken: 'cluck',
        },
        num: 1,
        cookie: exports.makeCookie(),
    };
};
exports.makeData = makeData;
const makeDataNoCookie = () => {
    return {
        foo: 'bar',
        baz: {
            cow: 'moo',
            fish: 'blub',
            fox: 'nobody knows!',
        },
        num: 2,
    };
};
exports.makeDataNoCookie = makeDataNoCookie;
const createStoreHelper = (opt = {}) => {
    const store = MongoStore_1.default.create({
        mongoUrl: 'mongodb://root:example@127.0.0.1:27017',
        mongoOptions: {},
        dbName: 'testDb',
        collectionName: 'test-collection',
        ...opt,
    });
    const storePromise = {
        length: util_1.default.promisify(store.length).bind(store),
        clear: util_1.default.promisify(store.clear).bind(store),
        get: util_1.default.promisify(store.get).bind(store),
        set: util_1.default.promisify(store.set).bind(store),
        all: util_1.default.promisify(store.all).bind(store),
        touch: util_1.default.promisify(store.touch).bind(store),
        destroy: util_1.default.promisify(store.destroy).bind(store),
        close: store.close.bind(store),
    };
    return { store, storePromise };
};
exports.createStoreHelper = createStoreHelper;
//# sourceMappingURL=data:application/json;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoidGVzdEhlbHBlci5qcyIsInNvdXJjZVJvb3QiOiIiLCJzb3VyY2VzIjpbIi4uLy4uLy4uL3NyYy90ZXN0L3Rlc3RIZWxwZXIudHMiXSwibmFtZXMiOltdLCJtYXBwaW5ncyI6Ijs7Ozs7O0FBQUEsK0RBQStEO0FBQy9ELHNFQUFzRTtBQUN0RSxnREFBdUI7QUFDdkIsc0VBQTRDO0FBRTVDLG1FQUFtRTtBQUVuRSxtQ0FBbUM7QUFDNUIsTUFBTSxVQUFVLEdBQUcsR0FBRyxFQUFFO0lBQzdCLE1BQU0sTUFBTSxHQUFHLElBQUkseUJBQWMsQ0FBQyxNQUFNLEVBQUUsQ0FBQTtJQUMxQyxNQUFNLENBQUMsTUFBTSxHQUFHLEtBQUssQ0FBQSxDQUFDLDJDQUEyQztJQUNqRSxNQUFNLENBQUMsTUFBTSxHQUFHLElBQUksQ0FBQTtJQUNwQixNQUFNLENBQUMsTUFBTSxHQUFHLFNBQVMsQ0FBQTtJQUN6QixNQUFNLENBQUMsUUFBUSxHQUFHLEtBQUssQ0FBQTtJQUV2QixPQUFPLE1BQU0sQ0FBQTtBQUNmLENBQUMsQ0FBQTtBQVJZLFFBQUEsVUFBVSxjQVF0QjtBQUVELHNCQUFzQjtBQUNmLE1BQU0sUUFBUSxHQUFHLEdBQUcsRUFBRTtJQUMzQixPQUFPO1FBQ0wsR0FBRyxFQUFFLEtBQUs7UUFDVixHQUFHLEVBQUU7WUFDSCxHQUFHLEVBQUUsS0FBSztZQUNWLE9BQU8sRUFBRSxPQUFPO1NBQ2pCO1FBQ0QsR0FBRyxFQUFFLENBQUM7UUFDTixNQUFNLEVBQUUsa0JBQVUsRUFBRTtLQUNyQixDQUFBO0FBQ0gsQ0FBQyxDQUFBO0FBVlksUUFBQSxRQUFRLFlBVXBCO0FBRU0sTUFBTSxnQkFBZ0IsR0FBRyxHQUFHLEVBQUU7SUFDbkMsT0FBTztRQUNMLEdBQUcsRUFBRSxLQUFLO1FBQ1YsR0FBRyxFQUFFO1lBQ0gsR0FBRyxFQUFFLEtBQUs7WUFDVixJQUFJLEVBQUUsTUFBTTtZQUNaLEdBQUcsRUFBRSxlQUFlO1NBQ3JCO1FBQ0QsR0FBRyxFQUFFLENBQUM7S0FDUCxDQUFBO0FBQ0gsQ0FBQyxDQUFBO0FBVlksUUFBQSxnQkFBZ0Isb0JBVTVCO0FBRU0sTUFBTSxpQkFBaUIsR0FBRyxDQUFDLE1BQW9DLEVBQUUsRUFBRSxFQUFFO0lBQzFFLE1BQU0sS0FBSyxHQUFHLG9CQUFVLENBQUMsTUFBTSxDQUFDO1FBQzlCLFFBQVEsRUFBRSx3Q0FBd0M7UUFDbEQsWUFBWSxFQUFFLEVBQUU7UUFDaEIsTUFBTSxFQUFFLFFBQVE7UUFDaEIsY0FBYyxFQUFFLGlCQUFpQjtRQUNqQyxHQUFHLEdBQUc7S0FDUCxDQUFDLENBQUE7SUFFRixNQUFNLFlBQVksR0FBRztRQUNuQixNQUFNLEVBQUUsY0FBSSxDQUFDLFNBQVMsQ0FBQyxLQUFLLENBQUMsTUFBTSxDQUFDLENBQUMsSUFBSSxDQUFDLEtBQUssQ0FBQztRQUNoRCxLQUFLLEVBQUUsY0FBSSxDQUFDLFNBQVMsQ0FBQyxLQUFLLENBQUMsS0FBSyxDQUFDLENBQUMsSUFBSSxDQUFDLEtBQUssQ0FBQztRQUM5QyxHQUFHLEVBQUUsY0FBSSxDQUFDLFNBQVMsQ0FBQyxLQUFLLENBQUMsR0FBRyxDQUFDLENBQUMsSUFBSSxDQUFDLEtBQUssQ0FBQztRQUMxQyxHQUFHLEVBQUUsY0FBSSxDQUFDLFNBQVMsQ0FBQyxLQUFLLENBQUMsR0FBRyxDQUFDLENBQUMsSUFBSSxDQUFDLEtBQUssQ0FBQztRQUMxQyxHQUFHLEVBQUUsY0FBSSxDQUFDLFNBQVMsQ0FBQyxLQUFLLENBQUMsR0FBRyxDQUFDLENBQUMsSUFBSSxDQUFDLEtBQUssQ0FBQztRQUMxQyxLQUFLLEVBQUUsY0FBSSxDQUFDLFNBQVMsQ0FBQyxLQUFLLENBQUMsS0FBSyxDQUFDLENBQUMsSUFBSSxDQUFDLEtBQUssQ0FBQztRQUM5QyxPQUFPLEVBQUUsY0FBSSxDQUFDLFNBQVMsQ0FBQyxLQUFLLENBQUMsT0FBTyxDQUFDLENBQUMsSUFBSSxDQUFDLEtBQUssQ0FBQztRQUNsRCxLQUFLLEVBQUUsS0FBSyxDQUFDLEtBQUssQ0FBQyxJQUFJLENBQUMsS0FBSyxDQUFDO0tBQy9CLENBQUE7SUFDRCxPQUFPLEVBQUUsS0FBSyxFQUFFLFlBQVksRUFBRSxDQUFBO0FBQ2hDLENBQUMsQ0FBQTtBQXBCWSxRQUFBLGlCQUFpQixxQkFvQjdCIn0=