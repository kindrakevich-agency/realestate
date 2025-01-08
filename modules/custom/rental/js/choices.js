Drupal.behaviors.rental = {
  attach: function (context, settings) {
    const radiosElements = document.querySelectorAll('.form-radios');
    if (radiosElements.length > 0) {
      radiosElements.forEach(function(radios) {
        radios.querySelectorAll('li').forEach(function(element) {
          const radioButton = element.querySelector('.form-radio');
          const label = element.querySelector('label');

          if (radioButton) {
            radioButton.classList.add('btn-check');
          }
          if (label) {
            label.classList.remove('option');
            label.classList.add('btn', 'btn-white', 'btn-primary-soft-check');
          }
        });
      });
    }
  }
}
