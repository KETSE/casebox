Single Sign-in with OAuth2
==============================

``cb__casebox.config``

``oauth2_credentials_google``

.. code-block:: json

    {
        "web": {
            "client_id": "49165442952-1b0ehojs8u4cm5b45fe0c2cifl06c3a3.apps.googleusercontent.com",
            "auth_uri": "https://accounts.google.com/o/oauth2/auth",
            "token_uri": "https://accounts.google.com/o/oauth2/token",
            "auth_provider_x509_cert_url": "https://www.googleapis.com/oauth2/v1/certs",
            "client_secret": "WWhE1KzxK36d7QPNLYg5mMnB",
            "redirect_uris": ["https://dev.casebox.org/oauth2callback"],
            "javascript_origins": ["https://dev.casebox.org"]
        }
    }