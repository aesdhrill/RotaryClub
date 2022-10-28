// REF: place this tag around radios that should reveal hidden sections:
// REF: <div class="form-group" data-toggle-map='{"#diabetes-data-sensor": 1, "#diabetes-data-pump": [0]}'>

let boolToInt = {true: '1', false: '0', null: ''};

function intersect(a, b) {
  let setB = new Set(b);
  return [...new Set(a)].filter(x => setB.has(x));
}

function isConditionalFieldRequired($this) {
  let condReq = $this.data('required-if');
  let condReqVal = Object.values(condReq);
  let isRequired = true;

  Object.keys(condReq).forEach(function (ee, v) {
    let condReqTarget = $this.closest('form').find('input[id^=' + ee + ']');

    switch (condReqTarget.attr('type')) {
      case 'checkbox':
        if ($('[type="checkbox"][name="' + condReqTarget.attr('name') + '"]').length === 1) {
          isRequired = ($('[type="checkbox"][name="' + condReqTarget.attr('name') + '"]').first().prop('checked') === condReqVal[v])
        } else {
          let checkedValues = $('input[name="' + condReqTarget.attr('name') + '"]:checked').map(function () {
            return $(this).val();
          }).get();

          let condReqVals = Array.isArray(condReqVal[v]) ? condReqVal[v] : [condReqVal[v]];

          if (intersect(condReqVals.map(e => [true, false, null].includes(e) ? boolToInt[e] : String(e)), checkedValues).length === 0) {
            isRequired = false;
          }
        }

        break;
      case 'radio':
        condReqTarget = condReqTarget.filter('[type="radio"]:checked');
        let condReqVals = Array.isArray(condReqVal[v]) ? condReqVal[v] : [condReqVal[v]];

        if (intersect(condReqVals.map(e => [true, false, null].includes(e) ? boolToInt[e] : String(e)), [condReqTarget.val()]).length === 0) {
          isRequired = false;
        }

        break;
    }
  });

  // console.log('isRequired', $this, isRequired)
  return isRequired;
}

export function applyConditionalRequirement($this) {
  let isRequired = isConditionalFieldRequired($this);

  if ($this.is('input')) {
    $this.prop('required', isRequired);
  } else {
    $this.find('input:not([type="checkbox"])').prop('required', isRequired);
  }

  $('label[for="' + $this.first().attr('id') + '"]').toggleClass('required', isRequired);
  $this.siblings('legend').toggleClass('required', isRequired);
}

function toggleConditionalSections($this) {
  let toggleMap = $this.closest('.form-group').data('toggle-map');
  if (!toggleMap) {
    return;
  }
  let inputType = $this.prop('type');
  let showTargets = []; // What is to be shown

  if ($this.attr('name').match(/Resolved]$/)) {
    return;
  }

  switch (inputType) {
    case 'checkbox':
      let checkedValues = $('input[name="' + $this.attr('name') + '"]:checked').map(function(){return $(this).val()}).get();
      showTargets = Object.entries(toggleMap).filter(({ 1: v }) => (Array.isArray(v) ? (intersect(checkedValues.map(String), v.map(String)).length !== 0) : checkedValues.map(String).includes(String(v)))).map(e => e[0]);

      break;

    case 'radio':
      let currVal = String($this.val());
      showTargets = Object.entries(toggleMap).filter(({ 1: v }) => (Array.isArray(v) ? v.map(String).includes(currVal) : String(v) === currVal)).map(e => e[0]);

      break;

    default:
      return;
  }
  // # TODO: przy choice type multiple nie robi się required, jeśli jest więcej niż jedna odpowiedź, przy której dochodzi dodatkowe pytanie
  Object.keys(toggleMap).forEach(function (toggleTarget) {
    if (toggleTarget.startsWith('#')) {
      $(toggleTarget).toggleClass('d-none', !showTargets.includes(toggleTarget));
    } else {
      $this.closest('.form-collection-item').find(`[data-toggle-id="${toggleTarget}"]`).toggleClass('d-none', !showTargets.includes(toggleTarget));
    }

    $(toggleTarget).find('[data-required-if]').each(function() {
      applyConditionalRequirement($(this));
    });
  });
}


let pageForms = $('form').get();
let previousFormsData = [];
let isChanged = false;

$(function () {
  $('[data-required-if]').each(function () {
    applyConditionalRequirement($(this));
  });

  $(document).on('change', 'input', function () {
    let inputFieldName = (/\[([a-zA-Z]+)\]$/g.exec($(this).attr('name')) || [null, null])[1];
    let inCollection = $(this).closest('.form-collection-item').length > 0;

    if (inputFieldName) {
      let requiredFields = inCollection
          ? $(this).closest('.form-collection-item').find('[data-required-if*="' + inputFieldName + '"]')
          : $('[data-required-if*="' + inputFieldName + '"]');

      requiredFields.each(function () {
        applyConditionalRequirement($(this));
      });
    }
  });

  $('.form-group[data-toggle-map] input:checked').each(function() {
    toggleConditionalSections($(this));
  });

  $(document).on('change', '.form-group[data-toggle-map] input', function () {
    toggleConditionalSections($(this));
  });

  // # TODO: also add this function on page load
  // # TODO: also for TextOrNone
  $('.number-or-none-group input').on('change keyup', function () {
    switch ($(this).attr('type')) {
      case 'text':
        let noDataCheckbox = $(this).closest('.number-or-none-group').find('input[type="checkbox"]');
        noDataCheckbox.prop("checked", $(this).val() === '');

        break;
      case 'checkbox':
        if ($(this).is(':checked')) {
          let valueInput = $(this).closest('.number-or-none-group').find('input[type="text"]');
          valueInput.prop('required', !$(this).prop('checked'));
          valueInput.val('');
        }
        // console.log($(this).prop('checked'))
    }
  });

  $('.cancel').on('click', function (event){
    event.preventDefault();
    let reaction = confirm('Czy na pewno chcesz odwołać tę wizytę?');
    if(reaction){window.location.replace($(this)[0].href)}
  });

  //Handle unsaved changes on page exit
  $('button[type="submit"]').on('click',function(e){ //Change this line to include a proper selector if necessary
    e.preventDefault();
    let inputs = $(this).closest('form').find('input');
    $(inputs).each(function(){
      $(this).css('outline', 'initial')
    })
    let invalidInputs = $(this).closest('form').find('input:invalid')

    if (!(invalidInputs.length > 0)) {
      $(window).off('beforeunload');
      $(this).off('click');
      $(this).trigger('click');
    } else {
      // This part focuses first invalid input
      let closestTab = $(invalidInputs).closest('.tab-pane');
      let id = closestTab.attr('id');
      if (typeof id !== "undefined") {
        $('[id="' + id + '-tab"]').tab('show');
      }
      $(invalidInputs[0]).focus();
      $(invalidInputs[0]).select();
      $(invalidInputs[0]).css('outline-style', 'solid');
      $(invalidInputs[0]).css('outline-width', '5px');
      $(invalidInputs[0]).css('outline-color', 'red');
      let elem_location_bounds=$(invalidInputs[0])[0].getBoundingClientRect()
      let container_bounds=$('#layoutSidenav_content')[0].getBoundingClientRect();
      window.scrollTo(window.scrollX, (elem_location_bounds.top - container_bounds.top - 15));
    }
  })

  $(window).on('beforeunload', function(e){
    let currentForms = $('form').get()
    let currentFormsData = [];
    if (currentForms.length !== 0) {
      for (let form in currentForms) {
        currentFormsData[form] = $(currentForms[form]).serializeArray();
        isChanged = !previousFormsData[form].every(
            function (v, i) {
              /*
                  A hacky workaround for TinyMCE editor empty field
                  Since TincyMCE v6.0 forced_root_block cannot be set to false, hence throwing <p><br data-mce-bogus="1"></p> into empty field
                  and does not remove it from model view on submit - however, it does not get sent do the database
                  It also inputs it for empty newline
                  Below is a workaround for previously-saved empty fields

                  related links
                  https://www.tiny.cloud/docs/tinymce/6/content-filtering/#forced_root_block
                  https://www.tiny.cloud/docs/tinymce/6/content-behavior-options/#newline_behavior
                  TODO: check if possible to fix
               */
              if (currentFormsData[form][i].value === '<p><br data-mce-bogus="1"></p>' && v.value === '') return true
              else return v.value === currentFormsData[form][i].value;
            });
      }
    } else e = null
    if (isChanged){
      return true;
    } else e = null
  })
});

// This is necessary due to $(document).ready() being asynchronous since jQuery 3.0
$(window).on('load', function(){
  $.ready.then(function(){
    if (pageForms.length !== 0) {
      for (let form in pageForms) {
        previousFormsData[form] = $(pageForms[form]).serializeArray();
      }
    }
  })
});
