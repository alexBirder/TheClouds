<script language="JavaScript" type="text/javascript">

function itemAdd(form){
	var messages = '';

	if(form.parent.value == 0 && form.edit.value == false) messages += "- Не выбрана рубрика.\n";
	if(form.title.value == '') messages += "- Не указано название.\n";

	if(messages.length == 0){
		form.submit();
	}
	else{
		alert(messages);
	}
}

function issueAdd(form){
	var messages = '';

	if(form.url.value == '') messages += "- Не указан URL.\n";
	if(form.title.value == '') messages += "- Не указан заголовок.\n";

	if(messages.length == 0){
		if(form.parent.value == 0 && form.edit.value == false){
			if(confirm('Добавить в корень?')) form.submit();
		}
		else{
			form.submit();
		}
	}
	else{
		alert(messages);
	}
}

</script>