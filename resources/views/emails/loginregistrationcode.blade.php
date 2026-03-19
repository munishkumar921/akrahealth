<!DOCTYPE html>
<html>

<head>
    <title>Patient Portal Registration</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />

    <style type="text/css">
        @media screen {
            @font-face {
                font-family: 'Lato';
                font-style: normal;
                font-weight: 400;
                src: local('Lato Regular'), local('Lato-Regular'),
                    url(https://fonts.gstatic.com/s/lato/v11/qIIYRU-oROkIk8vfvxw6QvesZW2xOQ-xsNqO47m55DA.woff) format('woff');
            }
        }

        body,
        table,
        td,
        a {
            -webkit-text-size-adjust: 100%;
            -ms-text-size-adjust: 100%;
        }

        table,
        td {
            mso-table-lspace: 0pt;
            mso-table-rspace: 0pt;
        }

        img {
            -ms-interpolation-mode: bicubic;
            border: 0;
            height: auto;
            line-height: 100%;
            outline: none;
            text-decoration: none;
        }

        table {
            border-collapse: collapse !important;
        }

        body {
            height: 100% !important;
            margin: 0 !important;
            padding: 0 !important;
            width: 100% !important;
            background-color: #f4f4f4;
        }

        a[x-apple-data-detectors] {
            color: inherit !important;
            text-decoration: none !important;
            font-size: inherit !important;
            font-family: inherit !important;
            font-weight: inherit !important;
            line-height: inherit !important;
        }

        @media screen and (max-width: 600px) {
            h1 {
                font-size: 26px !important;
                line-height: 32px !important;
            }
        }
    </style>
</head>

<body>
    <table border="0" cellpadding="0" cellspacing="0" width="100%">
        <!-- Logo -->
        <tr>
            <td bgcolor="#f8f8ff" align="center" style="padding: 20px 0;">
                <img src="{{ asset('images/logo.webp') }}" width="125" height="120"
                    alt="Akra Health" style="display: block;" />
            </td>
        </tr>

        <!-- Content -->
        <tr>
            <td align="center" style="padding: 10px;">
                <table border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 600px;">

                    <!-- Header -->
                    <tr>
                        <td bgcolor="#ffffff" align="center"
                            style="padding: 40px 20px 20px;
                               font-family: 'Lato', Helvetica, Arial, sans-serif;
                               color: #111111;">
                            <h1 style="font-size: 32px; font-weight: 400; margin: 0;">
                                You have been registered on the Patient Portal
                            </h1>
                            <p style="margin-top: 10px; font-size: 16px;">
                                {{ $practicename }}
                            </p>
                        </td>
                    </tr>

                    <!-- Message -->
                    <tr>
                        <td bgcolor="#ffffff" align="left"
                            style="padding: 20px 30px 30px;
                               font-family: 'Lato', Helvetica, Arial, sans-serif;
                               font-size: 16px;
                               line-height: 24px;
                               color: #666666;">
                            <p style="margin: 0 0 10px;">
                                Your registration code is:
                                <strong>
                                    <span style="color:#3facf8;">{{ $token }}</span>
                                </strong>
                            </p>

                            <p style="margin: 0;">
                                To complete your registration, please click the button below
                                to set your password and access your account.
                            </p>
                        </td>
                    </tr>

                    <!-- Button -->
                    <tr>
                        <td bgcolor="#ffffff" align="center" style="padding: 20px 30px 50px;">
                            <a href="{{ $url }}" target="_blank"
                                style="font-size: 18px;
                                  font-family: Helvetica, Arial, sans-serif;
                                  color: #ffffff;
                                  text-decoration: none;
                                  padding: 14px 24px;
                                  border-radius: 4px;
                                  background-color: #3facf8;
                                  display: inline-block;">
                                Complete Registration
                            </a>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>

</html>
