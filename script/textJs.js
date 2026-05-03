window.textsJs = {
    "del_mes":"Сообщение удалено", 
    "e_del_mes":"Произошла ошибка! Сообщение не удалено!",
    "no_mes":"<h1>У вас нет личных сообщений!</h1>",
    "serv_err":"Не правильный ответ от сервера, попробуйте снова.",
    "noFormLogin":"Не введен логин",
    "noFormPassw":"Не введен пароль",
    "erFormLogin":"Логин не может быть меньше 3-х символов",
    "erFormPassw":"Пароль не может быть меньше 6-ти символов",
    "ok_messages":"Прочитано",
    "go_mes":"Сообщение отправлено",
    "to_info":"Информация о тренере",
    "to_batl":"Принять вызов",
    "go_batl":"Бросить вызов",
    "pre_textBt":"Вы действительно хотите",
    "pre_textBr":"бросить",
    "pre_textPr":"принять",
    "pre_textCell":"вызов",
    "pre_textTo":" от: ",
    "pre_textYes":"ДА",
    "pre_textNo":"НЕТ",
    "pre_textIssetMes":"У Вас есть непрочитанные сообщения",
    "":""            
}                          

function translate_messages(message){
    if (typeof window.textsJs != 'undefined' && typeof window.textsJs[message] != 'undefined') {
        return window.textsJs[message];
    } else {
        return message;
    }
}