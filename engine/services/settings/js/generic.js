<script language="JavaScript" type="text/javascript">

function itemAdd(form){
	var messages = '';

	if(form.title.value == '') messages += "- Не указано название.\n";
	if(form.description.value == '') messages += "- Не указано описание.\n";
	if(form.keywords.value == '') messages += "- Не указаны ключевые слова.\n";

	if(messages.length == 0){
		form.submit();
	}
	else{
		alert(messages);
	}
}

function wordAdd(form){
	var messages = '';

	if(form.word.value == '') messages += "- Не указано название.\n";
	if(messages.length == 0){
		form.submit();
	} else{
		alert(messages);
	}
}

function menuAdd(form){
	var messages = '';

	if(form.title.value == '') messages += "- Не указано название.\n";
	if(messages.length == 0){
		form.submit();
	} else{
		alert(messages);
	}
}

function userAdd(form){
	var messages = '';

	if(form.name.value == '') messages += "- Не указано имя пользователя.\n";
	if(form.login_admin.value == '') messages += "- Не указан логин.\n";
	if(form.password_admin.value == '') messages += "- Не указан пароль.\n";
	if(messages.length == 0){
		form.submit();
	} else{
		alert(messages);
	}
}

</script>