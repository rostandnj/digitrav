
function getCookie(name)
{
    var re = new RegExp(name + "=([^;]+)");
    var value = re.exec(document.cookie);
    return (value != null) ? unescape(value[1]) : null;
}

function setCookie(name,value,days) {
    var expires = "";
    if (days) {
        var date = new Date();
        date.setTime(date.getTime() + (days*24*60*60*1000));
        expires = "; expires=" + date.toUTCString();
    }
    document.cookie = name + "=" + (value || "")  + expires + "; path=/";
}

$("#lang").on("click",function (event) {
    var name="lang";
    var lang=$("#lang_val").text();
    lang = lang.toLowerCase();
    lang = lang.substr(0,2);
    console.log(lang);
    setCookie(name,lang,30);
    location.reload();

});

function changeLang(lang)
{

}



$("#counterm").attr("style","min-height:"+$(window).height()+"px!important");