<?php 
  // get page
  $post = get_page_by_path('UFC Fighter', 'OBJECT');
  // get link to page
  $fighter_view_link = get_page_link($post);
?>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tarekraafat/autocomplete.js@10.2.7/dist/css/autoComplete.min.css" />
<div class="container">
  	<div class="body" style="display: flex;flex-flow:column nowrap;align-items: center;justify-content: center">
		<div>Start Typing to Discover Fighter by Name</div>
    	<div class="ufc_datatable_autocomplete_wrapper">
			<br />
      		<input id="ufc_datatable_autocomplete" type="search" dir="ltr" spellcheck=false autocorrect="off" autocomplete="off" autocapitalize="off" maxlength="2048" tabindex="1">
    	</div>
		<div class="ufc_datatable_selection"></div>
	</div>
</div>
<script src="https://cdn.jsdelivr.net/npm/@tarekraafat/autocomplete.js@10.2.7/dist/autoComplete.min.js"></script>
<script>
  // The autoComplete.js Engine instance creator
  const autoCompleteJS = new autoComplete({
    selector: "#ufc_datatable_autocomplete",
    data: {
      src: [],
      keys: ["fighter_name"],
      cache: true
    },
    placeHolder: "Search for Fighter!",
    
    events: {
      input: {
        focus: () => {
          if (autoCompleteJS.input.value.length) autoCompleteJS.start();
        }
      }
    },
    query: (query) => {
      // send post request
      jQuery.post("<?= admin_url( 'admin-ajax.php' ) ?>", {
        query,
        action: "ufc_datatable_fighter_ajax_search",
        "_wpnonce": "<?= wp_create_nonce('ufc_datatable_fighter_ajax_search') ?>"
      }, function(response) {
        if(!response.rows.length) return;
        autoCompleteJS.data.store = response.rows;
        autoCompleteJS.open();
      }).catch((e) => {});
      // return
      return query;
    },
    debounce: 300,
  });

  autoCompleteJS.input.addEventListener("selection", function (event) {
    const feedback = event.detail;
    autoCompleteJS.input.blur();
    // Prepare User's Selected Value
    const selection = feedback.selection.value[feedback.selection.key];
    // Replace Input value with the selected value
    autoCompleteJS.input.value = selection;
    // Open in new page
    window.location.href = `<?= $fighter_view_link ?>?ID=${feedback.selection.value.fighter_id}`
  });
</script>
<style>
.ufc_datatable_no_result {
  margin: 0.3rem;
  padding: 0.3rem 0.5rem;
  list-style: none;
  text-align: left;
  font-size: 1rem;
  color: #212121;
  transition: all 0.1s ease-in-out;
  border-radius: 0.35rem;
  background-color: rgba(255, 255, 255, 1);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  transition: all 0.2s ease;
  outline: none;
}

.ufc_datatable_selection {
  margin-top: 35vh;
  font-size: 2rem;
  font-weight: bold;
  color: #ffc6c6;
  transition: var(--transition-1);
}

.ufc_datatable_selection::selection {
  color: #64ceaa;
}
.autoComplete_wrapper>input {
	width: 370px;
}
</style>