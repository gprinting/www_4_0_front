var getCuttingPrice = function (aft, dvs) {
    var prefix = getPrefix(dvs) + aft;
    var name = $(prefix).val();
    var depth1 = $(prefix + "_depth1").val();
    var depth2 = $(prefix + "_val > option:selected").text();
    var amt = $(getPrefix(dvs) + "amt").val();

    chkRingFourDirection(aft, dvs, depth1);

    var data = {
        "after_name": name,
        "depth1": depth1,
        "depth2": depth2,
        "amt": amt
    };

    getAoAfterPrice.load(aft, data, dvs,
        getAoAfterPrice.basicCallback(aft, dvs));
};

var getCoolCoatingPrice = function (aft, dvs) {
    var prefix = getPrefix(dvs) + aft;
    var name = $(prefix).val();
    var depth1 = $(prefix + "_val > option:selected").text();
    var amt = $(getPrefix(dvs) + "amt").val();

    chkRingFourDirection(aft, dvs, depth1);

    var data = {
        "cate_sortcode": sortcode,
        "after_name": name,
        "depth1": depth1,
        "amt": amt
    };

    getAoAfterPrice.load(aft, data, dvs,
        getAoAfterPrice.basicCallback(aft, dvs));
};

var getRingPrice = function (aft, dvs) {
    var prefix = getPrefix(dvs) + aft;
    var name = $(prefix).val();
    var depth1 = $(prefix + "_val > option:selected").text();
    var amt = $(getPrefix(dvs) + "amt").val();

    chkRingFourDirection(aft, dvs, depth1);

    var data = {
        "cate_sortcode": sortcode,
        "after_name": name,
        "depth1": depth1,
        "amt": amt,
        "t_cnt": $(prefix + "_t").val(),
        "b_cnt": $(prefix + "_b").val(),
        "l_cnt": $(prefix + "_l").val(),
        "r_cnt": $(prefix + "_r").val()
    };

    var callback = function (result) {
        setAfterPrice(dvs, aft, result.price);
    };

    getAoAfterPrice.load(aft, data, dvs, callback);
};

var getDotlinePrice = function (aft, dvs) {
    var prefix = getPrefix(dvs) + aft;
    var name = $(prefix).val();
    var depth1 = $(prefix + "_depth1").val();
    var depth2 = $(prefix + "_val > option:selected").text();
    var amt = $(getPrefix(dvs) + "amt").val();

    var t = $(prefix + "_t").prop("checked") ? 1 : 0;
    var b = $(prefix + "_b").prop("checked") ? 1 : 0;
    var l = $(prefix + "_l").prop("checked") ? 1 : 0;
    var r = $(prefix + "_r").prop("checked") ? 1 : 0;

    var data = {
        "cate_sortcode": sortcode,
        "after_name": name,
        "depth1": depth1,
        "depth2": depth2,
        "amt": amt,
        "wid": $("#cut_wid_size").val(),
        "vert": $("#cut_vert_size").val(),
        "t_pos": t,
        "b_pos": b,
        "l_pos": l,
        "r_pos": r
    };

    getAoAfterPrice.load(aft, data, dvs,
        getAoAfterPrice.basicCallback(aft, dvs));
};

var getScrollPrice = function (aft, dvs) {
    var prefix = getPrefix(dvs) + aft;
    var name = $(prefix).val();
    var depth1 = $(prefix + "_depth1").val();
    var amt = $(getPrefix(dvs) + "amt").val();

    var data = {
        "after_name": name,
        "depth1": depth1,
        "amt": amt,
        "wid": $("#cut_wid_size").val(),
        "vert": $("#cut_vert_size").val(),
        "t_pos": "true",
        "b_pos": "true",
        "l_pos": "false",
        "r_pos": "false"
    };

    getAoAfterPrice.load(aft, data, dvs,
        getAoAfterPrice.basicCallback(aft, dvs));
};

var getPunchingPrice = function (aft, dvs) {};