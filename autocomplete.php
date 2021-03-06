<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
<script>
	var selectionIndex;

	function autocompleteIngredient(textbox, id, event) {
		//what is inside the textbox (user input)
		var text = textbox.value;
		
		//what the user "cursor" was on before keyUp of another key
		var oldIndex = selectionIndex;
		var close = false;
		if (event.keyCode == 38) { selectionIndex--; }	//38 = up arrow key
		if (event.keyCode == 40) { selectionIndex++; }  //40 = down arrow key
		if (event.keyCode == 13) { close = true;	 }	//13 = enter
		selectionIndex = selectionIndex < -1 ? -1 : selectionIndex;
		selectionIndex = selectionIndex >  9 ?  9 : selectionIndex;
		
		//Check if our database has anything that starts with text (only if something changed)
		if (selectionIndex == oldIndex) {
			$("#autocompleteDiv" + id).load("database/autocompleteSearch.php?id=" + escape(id) + "&query=" + escape(text));	
		}
		
		//Highlight whatever object is at the correct index
		$("#autocompleteDivSelection" + id + selectionIndex).click();
		$("#autocompleteDivSelection" + id + selectionIndex).trigger("mouseenter");
		$("#autocompleteDivSelection" + id + oldIndex).trigger("mouseleave");
		$("#autocompleteDivSelection" + id + oldIndex).trigger("click");

		
		if(event.click()) { close - true; }
		
		//get rid of the autocomplete options if the user pushes enter or clicks the mouse
		if (close) {
			$(textbox).blur();
		}
	}
	
	function hideAutocompleteDiv(id) {
		$("#autocompleteDiv" + id).hide(200);
	}
	
	function showAutocompleteDiv(id) {
		selectionIndex = -1;
		$("#autocompleteDiv" + id).show(200);
	}
	
	function createAutocompleteTextbox(id) {
		return " "
			+ "<input type='text' class='form-control' id='measurement" + id + "'>"
			+ "<input type='text' class='form-control' z-index='5' name='" + id + "' id='" + id + "' onKeyUp='autocompleteIngredient(this,\"" + id + "\",event)' autocomplete='off' onBlur='hideAutocompleteDiv(\"" + id + "\")' onFocus='showAutocompleteDiv(\"" + id + "\")'>"
			+ "<div id='autocompleteDiv" + id + "' style='background-color: #fff; border: 1px solid #ccc; padding-left: 5px; padding-right: 5px; display: none; position: absolute'></div>"
	}
</script>

<script>

//document.write(createAutocompleteTextbox('a'));

</script>


