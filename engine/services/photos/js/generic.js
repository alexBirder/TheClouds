<script language="JavaScript" type="text/javascript">

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