var bus_direction = 0;
var page_height = $(window).width() <= 500 ? $(window).height() : 585;
var page_width = $(window).width() <= 500 ? $(window).width() : 337;
$('#iframe-seacrh').css('height',page_height);
$('#iframe-seacrh').css('width',page_width);
$('#iframe-result').css('height',page_height);
$('#iframe-result').css('width',page_width);
$("#iframe-result > .bottom > .box").css('height',page_height-46);

Date.prototype.format = function(format){
    var o = {
        "M+" : this.getMonth()+1,
        "d+" : this.getDate(),
        "h+" : this.getHours(),
        "m+" : this.getMinutes(),
        "s+" : this.getSeconds(),
        "q+" : Math.floor((this.getMonth()+3)/3),
        "S" : this.getMilliseconds()
    };
    if(/(y+)/i.test(format)) {
        format = format.replace(RegExp.$1, (this.getFullYear()+"").substr(4 - RegExp.$1.length));
    }
    for(var k in o) {
        if(new RegExp("("+ k +")").test(format)) {
            format = format.replace(RegExp.$1, RegExp.$1.length==1 ? o[k] : ("00"+ o[k]).substr((""+ o[k]).length));
        }
    }
    return format;
};

var now = new Date();
var nowStr = now.format("yyyy-MM-dd hh:mm");
$('#demo_datetime').val(nowStr);

if(!$.cookie('user_id')){
    var time = String(Date.parse(now)/1000);
    time = time.substring(5);
    var random = String(parseInt(Math.random()*(999-100+1)+100));
    $.cookie('user_id', time+random, { expires: 365*10 });
}if(!$.cookie('jr_switch')){
    $.cookie('jr_switch', 1, { expires: 365*10 });
}if(!$.cookie('jr_direction')){
    $.cookie('jr_direction', 0, { expires: 365*10 });
}

$('.change').click(function(){
    if(bus_direction != 0){
        $('.school').html("学校");
        $('.train').html("駅");
        bus_direction = 0;
    }else{
        $('.school').html("駅");
        $('.train').html("学校");
        bus_direction = 1;
    }
});

$('#submit').click(function(){
    history.pushState({title:"login"}, "login", "");

    if(bus_direction == 0) $('#iframe-result>.top>.top-middle').html('検索結果(学校 → 駅)');
    if(bus_direction == 1) $('#iframe-result>.top>.top-middle').html('検索結果(駅 → 学校)');

    $('#iframe-seacrh').animate({'margin-left':'-'+page_width},200);

    mydata=$('#demo_datetime').val().replace(/-/g, '/');
    $.ajax({
        url: './index.php',
        data: {
            'user_id' : $.cookie('user_id') ,
            'jr_switch' :  $.cookie('jr_switch'),
            'jr_direction' : $.cookie('jr_direction'),
            'bus_direction' : bus_direction ,
            'time' : Date.parse(mydata)/1000
        },
        type: 'GET',
        dataType: 'html',
        success: function(date){
            if(date != 'none'){
                $('#iframe-result>.bottom>.box').html(date);
                setTimeout(function(){
                    $('.loading').hide();
                    $('#iframe-result>.bottom').show();
                    $('#iframe-result>.bottom>.box').animate({
                        scrollTop:$('#iframe-result>.bottom>.box>.ok').offset().top-(page_height/2-50)
                    },200);
                },500)
            }else{
                setTimeout(function(){
                    $('#iframe-result>.bottom').hide();
                    $('.loading').hide();
                    $('.no_date>div').html('本日はバスの運行はありません。').parent().show();
                },500)
            }
        },
        error: function(){
            setTimeout(function(){
                $('#iframe-result>.bottom').hide();
                $('.loading').hide();
                $('.no_date>div').html('ネットの違い。').parent().show();
            },500)
        }
    });

});

$('.button-back').click(function(e){
    // history.pushState({title:"login"}, "login", "");
    $('#iframe-seacrh').animate({'margin-left':'0'},300);
    setTimeout(function(){
        $('#iframe-result>.bottom>.box').scrollTop(0).html('');
        $('#iframe-result>.bottom').hide();
        $('#iframe-result>.loading').show();
        $('.no_date').hide();
    },300);
})

// Date & Time demo initialization
$('#demo_datetime').mobiscroll().datetime({
    theme: "ios",
    mode: "scroller",
    display: "bottom",
    lang: "jp",
    dateFormat:"yy-mm-dd",
    minDate: new Date(2018,1,1,1,1),
    maxDate: new Date(2022,12,31,23,59),
    stepMinute: 1
});

history.pushState && window.addEventListener("popstate", function(e) {
    $('.button-back').click()
}, false);