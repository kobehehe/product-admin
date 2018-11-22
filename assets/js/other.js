

Date.prototype.Format = function (fmt) { //author: meizz 
    var o = {
        "M+": this.getMonth() + 1, //月份 
        "d+": this.getDate(), //日 
        "h+": this.getHours(), //小时 
        "m+": this.getMinutes(), //分 
        "s+": this.getSeconds(), //秒 
        "q+": Math.floor((this.getMonth() + 3) / 3), //季度 
        "S": this.getMilliseconds() //毫秒 
    };
    if (/(y+)/.test(fmt)) fmt = fmt.replace(RegExp.$1, (this.getFullYear() + "").substr(4 - RegExp.$1.length));
    for (var k in o)
        if (new RegExp("(" + k + ")").test(fmt)) fmt = fmt.replace(RegExp.$1, (RegExp.$1.length == 1) ? (o[k]) : (("00" +
            o[k]).substr(("" + o[k]).length)));
    return fmt;
}
function uniq(array) {
    var temp = [array[0]];
    var arr = []
    for(var i = 1; i < array.length; i++) {
        if(array[i].order_id !== temp[temp.length - 1].order_id) {
            temp.push(array[i]);
        }
    }
    for(var i in temp) {
        var ret = array.filter((e) => {
            return e.order_id == temp[i].order_id

        })
        arr.push(ret)
    }
    
    return arr ;

}

function enTranslationZh(key){
    var obj={
        "United States":'美国',
        "United Kingdom":'英国',
        "Austria":'奥地利',
        "Canada":'加拿大',
        "Singapore":'新加坡',
        "Spain":'西班牙',
        "Australia":'澳大利亚'
    }
    if(obj[key]==undefined){
        return key
    }
    return obj[key]
}

