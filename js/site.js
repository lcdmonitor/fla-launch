// Example starter JavaScript for disabling form submissions if there are invalid fields
(function () {
    'use strict'

    // Fetch all the forms we want to apply custom Bootstrap validation styles to
    var forms = document.querySelectorAll('.needs-validation')

    // Loop over them and prevent submission
    Array.prototype.slice.call(forms)
        .forEach(function (form) {
            form.addEventListener('submit', function (event) {
                if (!form.checkValidity()) {
                    event.preventDefault()
                    event.stopPropagation()
                }

                form.classList.add('was-validated')
            }, false)
        })
})()


/* AJAX Function */
function AJAXform(formID, formMethod = 'post') {
    this.formAction = document.getElementById(formID).getAttribute('action'); // Get the form action.
    this.formInputs = document.getElementById(formID).querySelectorAll("input"); // Get the form inputs.
    this.formMethod = formMethod;
}

AJAXform.prototype.XMLhttp = function () {
    var httpRequest = new XMLHttpRequest();
    var formData = new FormData();

    for (var i = 0; i < this.formInputs.length; i++) {
        formData.append(this.formInputs[i].name, this.formInputs[i].value); // Add all inputs inside formData().
    }

    httpRequest.onreadystatechange = function () {

        if (this.readyState === XMLHttpRequest.DONE) {
            if (this.status == 200) {
                    //Do Something with a cool callback
            }
            else {
                alert('Invalid Username or Password');
                console.log(this.responseText);
            }
        }

    };

    alert('sending');
    httpRequest.open(this.formMethod, this.formAction);
    httpRequest.send(formData);
}

function processLogin() {
    var forms = document.querySelectorAll('.needs-validation')
    Array.prototype.slice.call(forms)
        .forEach(function (form) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            else {
                form.classList.add('was-validated');

                var req = new AJAXform('loginForm');

                req.XMLhttp();
            }

        });
    return false;
}
