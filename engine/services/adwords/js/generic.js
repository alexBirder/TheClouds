<script language="JavaScript" type="text/javascript">

function fileAdd(form){
	var messages = '';

	if(form.title.value == '') messages += "- Не указано название.\n";
	if(form.image.value == '' && form.edit.value == false) messages += "- Не выбран файл.\n";

	if(messages.length == 0){
		form.submit();
	}
	else{
		alert(messages);
	}
}

</script>