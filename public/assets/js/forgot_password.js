 // Wait for the document to be fully loaded
        document.addEventListener("DOMContentLoaded", function() {
            // Find the alert message element
            var alertMessage = document.getElementById("alert-message");

            // If the alert message exists
            if (alertMessage) {
                // Set a timeout to hide it after 3500 milliseconds (3.5 seconds)
                setTimeout(function() {
                    // Use Bootstrap's alert 'close' method if available, or just hide it
                    if (bootstrap.Alert.getInstance(alertMessage)) {
                        bootstrap.Alert.getInstance(alertMessage).close();
                    } else {
                        alertMessage.style.display = 'none';
                    }
                }, 3500);
            }
        });