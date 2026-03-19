<!DOCTYPE html>
<html>

<head>
    <title>{{ isset($data['subject']) ? $data['subject'] : 'New Message' }}</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <style type="text/css">
        @media screen {
            @font-face {
                font-family: 'Montserrat';
                font-style: normal;
                font-weight: 400;
                src: local('Montserrat'), local('Montserrat');
            }

            @font-face {
                font-family: 'Montserrat';
                font-style: normal;
                font-weight: 700;
                src: local('Montserrat Bold'), local('Montserrat-Bold');
            }

            /* CLIENT-SPECIFIC STYLES */
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
            }

            /* RESET STYLES */
            img {
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
            }

            /* iOS BLUE LINKS */
            a[x-apple-data-detectors] {
                color: inherit !important;
                text-decoration: none !important;
                font-size: inherit !important;
                font-family: inherit !important;
                font-weight: inherit !important;
                line-height: inherit !important;
            }

            /* MOBILE STYLES */
            @media screen and (max-width:600px) {
                h1 {
                    font-size: 32px !important;
                    line-height: 32px !important;
                }
            }

            /* ANDROID CENTER FIX */
            div[style*="margin: 16px 0;"] {
                margin: 0 !important;
            }
        }
    </style>
</head>

<body style="background-color: #f4f4f4; margin: 0 !important; padding: 0 !important;">
    <!-- HIDDEN PREHEADER TEXT -->
    <div style="display: none; font-size: 1px; color: #fefefe; line-height: 1px; font-family: 'Montserrat', Helvetica, Arial, sans-serif; max-height: 0px; max-width: 0px; opacity: 0; overflow: hidden;">
        You have a new message from {{ isset($data['sender_name']) ? $data['sender_name'] : 'Doctor' }}
    </div>
    <table border="0" cellpadding="0" cellspacing="0" width="100%">
        <!-- LOGO -->
        <tr>
            <td bgcolor="#f4f4f4" align="center">
                <table border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 600px;">
                    <tr>
                        <td align="center" valign="top" style="padding: 40px 10px 40px 10px;"> </td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td bgcolor="#f4f4f4" align="center" style="padding: 0px 10px 0px 10px;">
                <table border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 600px;">
                    <tr>
                        <td bgcolor="#ffffff" align="center" valign="top"
                            style="padding: 20px 30px 40px 30px; border-radius: 2px 2px 0px 0px; font-family: 'Montserrat', Helvetica, Arial, sans-serif; font-size: 45px; font-weight: 700; letter-spacing: 2px; line-height: 48px;">
                            <img src="{{ asset('images/logo.webp') }}" width="125" height="120"
                                style="display: block; border: 0px;" />
                        </td>
                    </tr>
                    <tr>
                        <td bgcolor="#ffffff" align="left"
                            style="padding: 20px 30px 0px 30px; color: #666666; font-family: 'Lato', Helvetica, Arial, sans-serif; font-size: 18px; font-weight: 400; line-height: 25px;">
                            <p style="margin: 0;">Hello,</p>
                        </td>
                    </tr>
                    <tr>
                        <td bgcolor="#ffffff" align="left"
                            style="padding: 20px 30px 0px 30px; color: #666666; font-family: 'Lato', Helvetica, Arial, sans-serif; font-size: 18px; font-weight: 400; line-height: 25px;">
                            <p style="margin: 0;">You have received a new message from <strong>{{ isset($data['sender_name']) ? $data['sender_name'] : 'Doctor' }}</strong>.</p>
                        </td>
                    </tr>
                    @if(isset($data['patient_name']))
                    <tr>
                        <td bgcolor="#ffffff" align="left"
                            style="padding: 20px 30px 0px 30px; color: #666666; font-family: 'Lato', Helvetica, Arial, sans-serif; font-size: 18px; font-weight: 400; line-height: 25px;">
                            <p style="margin: 0;">Patient: <strong>{{ $data['patient_name'] }}</strong></p>
                        </td>
                    </tr>
                    @endif
                    <tr>
                        <td bgcolor="#ffffff" align="left"
                            style="padding: 20px 30px 0px 30px; color: #666666; font-family: 'Lato', Helvetica, Arial, sans-serif; font-size: 18px; font-weight: 400; line-height: 25px;">
                            <p style="margin: 0;"><strong>Subject:</strong> {{ isset($data['subject']) ? $data['subject'] : 'No Subject' }}</p>
                        </td>
                    </tr>
                    <tr>
                        <td bgcolor="#ffffff" align="left"
                            style="padding: 20px 30px 0px 30px; color: #666666; font-family: 'Lato', Helvetica, Arial, sans-serif; font-size: 18px; font-weight: 400; line-height: 25px;">
                            <p style="margin: 0;"><strong>Message:</strong></p>
                            <p style="margin: 10px 0 0 0;">{{ isset($data['message']) ? $data['message'] : '' }}</p>
                        </td>
                    </tr>
                    @if(isset($data['cc']) && count($data['cc']) > 0)
                    <tr>
                        <td bgcolor="#ffffff" align="left"
                            style="padding: 20px 30px 0px 30px; color: #666666; font-family: 'Lato', Helvetica, Arial, sans-serif; font-size: 18px; font-weight: 400; line-height: 25px;">
                            <p style="margin: 0;"><strong>CC:</strong> {{ implode(', ', $data['cc']) }}</p>
                        </td>
                    </tr>
                    @endif
                    <tr>
                        <td bgcolor="#ffffff" align="left"
                            style="padding: 0px 30px 40px 30px; border-radius: 0px 0px 4px 4px; color: #666666; font-family: 'Lato', Helvetica, Arial, sans-serif; font-size: 18px; font-weight: 400; line-height: 25px;">
                            <p style="margin: 0;">With Regards,<br>Akrahealth Team.</p>
                        </td>
                    </tr>
                    <tr>
                        <td bgcolor="#ffffff" align="left"
                            style="padding: 0px 30px 20px 30px; color: #666666; font-family: 'Lato', Helvetica, Arial, sans-serif; font-size: 18px; font-weight: 400; line-height: 25px;">
                            <p style="margin: 0;">Explore our EMR/EHR Services, Pharmacy App & our EMR App for details!</p>
                        </td>
                    </tr>
                    <tr>
                        <td bgcolor="#ffffff" align="left"
                            style="padding: 0px 30px 40px 30px; border-radius: 0px 0px 4px 4px; color: #666666; font-family: 'Lato', Helvetica, Arial, sans-serif; font-size: 18px; font-weight: 400; line-height: 25px;">
                            <p style="margin: 0;">In case of any queries, write to us at: contact@akrahealth.com,<br>Or, get in touch on call at +91 6381 250 184</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>

</html>

