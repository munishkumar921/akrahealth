<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Order Notification</title>
    <style>
        /* Reset styles */
        body, html { margin: 0; padding: 0; width: 100%; height: 100%; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif; font-size: 16px; line-height: 1.6; color: #333333; background-color: #f4f6f8; }
        
        /* Container */
        .email-wrapper { width: 100%; background-color: #f4f6f8; padding: 40px 20px; }
        .email-container { max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08); }
        
        /* Header */
        .email-header { background: linear-gradient(135deg, #059669 0%, #047857 100%); padding: 40px 30px; text-align: center; }
        .email-header h1 { color: #ffffff; margin: 0; font-size: 24px; font-weight: 600; }
        .email-header .icon { font-size: 48px; margin-bottom: 15px; }
        
        /* Content */
        .email-content { padding: 40px 30px; }
        .greeting { font-size: 20px; font-weight: 600; color: #1e293b; margin-bottom: 20px; }
        .intro-text { color: #475569; margin-bottom: 25px; }
        
        /* Info Cards */
        .info-card { background-color: #f8fafc; border-radius: 10px; padding: 25px; margin-bottom: 20px; border: 1px solid #e2e8f0; }
        .info-card-title { font-size: 14px; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 15px; padding-bottom: 10px; border-bottom: 1px solid #e2e8f0; }
        .detail-row { display: flex; margin-bottom: 12px; }
        .detail-row:last-child { margin-bottom: 0; }
        .detail-label { font-weight: 600; color: #374151; min-width: 120px; flex-shrink: 0; }
        .detail-value { color: #475569; word-break: break-word; }
        
        /* Order Type Badges */
        .order-badge { display: inline-block; background-color: #d1fae5; color: #047857; padding: 4px 12px; border-radius: 20px; font-size: 13px; font-weight: 600; margin-right: 8px; margin-bottom: 8px; }
        .order-badge.labs { background-color: #dbeafe; color: #1d4ed8; }
        .order-badge.radiology { background-color: #fef3c7; color: #d97706; }
        .order-badge.cp { background-color: #f3e8ff; color: #7c3aed; }
        .order-badge.referrals { background-color: #fce7f3; color: #db2777; }
        
        /* ICD Codes */
        .icd-section { margin-top: 15px; }
        .icd-code { display: inline-block; background-color: #f1f5f9; color: #475569; padding: 3px 10px; border-radius: 4px; font-size: 13px; margin-right: 6px; margin-bottom: 6px; font-family: monospace; }
        
        /* Footer */
        .email-footer { background-color: #f8fafc; padding: 30px; text-align: center; border-top: 1px solid #e2e8f0; }
        .footer-text { color: #64748b; font-size: 13px; margin: 0 0 10px 0; }
        .footer-link { color: #059669; text-decoration: none; }
        
        /* Divider */
        .divider { height: 1px; background-color: #e2e8f0; margin: 25px 0; }
        
        /* Responsive */
        @media only screen and (max-width: 640px) {
            .email-wrapper { padding: 20px 15px; }
            .email-container { border-radius: 8px; }
            .email-header { padding: 30px 20px; }
            .email-content { padding: 30px 20px; }
            .email-footer { padding: 20px; }
            .detail-row { flex-direction: column; }
            .detail-label { margin-bottom: 4px; }
        }
    </style>
</head>
<body>
    <div class="email-wrapper">
        <div class="email-container">
            <!-- Header -->
            <div class="email-header">
                <div class="icon">📋</div>
                <h1>New Order Placed</h1>
            </div>
            
            <!-- Content -->
            <div class="email-content">
                <div class="greeting">Hello,</div>
                <p class="intro-text">A new order has been placed for your review.</p>
                
                <!-- Order Information -->
                <div class="info-card">
                    <div class="info-card-title">Order Information</div>
                    
                    <div class="detail-row">
                        <span class="detail-label">Order ID:</span>
                        <span class="detail-value">{{ $order->id }}</span>
                    </div>
                    
                    <div class="detail-row">
                        <span class="detail-label">Order Date:</span>
                        <span class="detail-value">{{ $order->orders_date ? \Carbon\Carbon::parse($order->orders_date)->format('M d, Y') : 'N/A' }}</span>
                    </div>
                    
                    <div class="detail-row">
                        <span class="detail-label">Order Type:</span>
                        <span class="detail-value">
                            @if($order->labs)
                                <span class="order-badge labs">Laboratory</span>
                            @endif
                            @if($order->radiology)
                                <span class="order-badge radiology">Radiology</span>
                            @endif
                            @if($order->cp)
                                <span class="order-badge cp">Cardiopulmonary</span>
                            @endif
                            @if($order->referrals)
                                <span class="order-badge referrals">Referral</span>
                            @endif
                        </span>
                    </div>
                    
                    <div class="detail-row" style="flex-direction: column;">
                        <span class="detail-label">Order Details:</span>
                        <span class="detail-value" style="margin-top: 8px;">
                            {{ $order->labs ?? $order->referrals ?? $order->radiology ?? $order->cp ?? 'N/A' }}
                        </span>
                    </div>
                    
                    <!-- ICD Codes -->
                    @if($order->referrals_icd || $order->labs_icd || $order->radiology_icd || $order->cp_icd)
                        <div class="icd-section">
                            <span class="detail-label">ICD Codes:</span>
                            <div style="margin-top: 8px;">
                                @if($order->referrals_icd)
                                    @foreach(explode(',', $order->referrals_icd) as $code)
                                        <span class="icd-code">{{ trim($code) }}</span>
                                    @endforeach
                                @endif
                                @if($order->labs_icd)
                                    @foreach(explode(',', $order->labs_icd) as $code)
                                        <span class="icd-code">{{ trim($code) }}</span>
                                    @endforeach
                                @endif
                                @if($order->radiology_icd)
                                    @foreach(explode(',', $order->radiology_icd) as $code)
                                        <span class="icd-code">{{ trim($code) }}</span>
                                    @endforeach
                                @endif
                                @if($order->cp_icd)
                                    @foreach(explode(',', $order->cp_icd) as $code)
                                        <span class="icd-code">{{ trim($code) }}</span>
                                    @endforeach
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
                
                <!-- Patient Details -->
                <div class="info-card">
                    <div class="info-card-title">Patient Details</div>
                    
                    <div class="detail-row">
                        <span class="detail-label">Name:</span>
                        <span class="detail-value">{{ $patient->user->name ?? 'N/A' }}</span>
                    </div>
                    
                    <div class="detail-row">
                        <span class="detail-label">Email:</span>
                        <span class="detail-value">{{ $patient->user->email ?? 'N/A' }}</span>
                    </div>
                </div>
                
                <!-- Provider Details -->
                <div class="info-card">
                    <div class="info-card-title">Provider Details</div>
                    
                    <div class="detail-row">
                        <span class="detail-label">Name:</span>
                        <span class="detail-value">{{ $doctor->user->name ?? 'N/A' }}</span>
                    </div>
                    
                    <div class="detail-row">
                        <span class="detail-label">Email:</span>
                        <span class="detail-value">{{ $doctor->user->email ?? 'N/A' }}</span>
                    </div>
                </div>
                
                <div class="divider"></div>
                
                <p style="color: #64748b; font-size: 14px; text-align: center;">
                    Please review this order at your earliest convenience.
                </p>
            </div>
            
            <!-- Footer -->
            <div class="email-footer">
                <p class="footer-text">This is an automated notification from AKRA Health.</p>
                <p class="footer-text" style="margin: 0;">Please do not reply directly to this email.</p>
            </div>
        </div>
    </div>
</body>
</html>

