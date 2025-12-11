<?php
// payment.php
session_start();
// Itt lehetne a rendelés ID-t átvenni sessionből vagy GET paraméterből
$order_number = 'ORD-' . rand(100000, 999999); // Demo
$total_amount = isset($_SESSION['order_total']) ? $_SESSION['order_total'] : 0;
?>
<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Techoazis | Shop</title>
    <link rel="icon" type="image/x-icon" href="./images/palmtree_favicon.svg">
    <link rel="stylesheet" href="./static/index.css">
    <link rel="stylesheet" href="./static/reset&base_styles.css">
    <link rel="stylesheet" href="./static/animations_microinteractions.css">
    <link rel="stylesheet" href="./static/button_system.css">
    <link rel="stylesheet" href="./static/comments.css">
    <link rel="stylesheet" href="./static/container&grid_system.css">
    <link rel="stylesheet" href="./static/create_post.css">
    <link rel="stylesheet" href="./static/custom_card.css">
    <link rel="stylesheet" href="./static/feature_cards.css">
    <link rel="stylesheet" href="./static/filter_system.css">
    <link rel="stylesheet" href="./static/forum.css">
    <link rel="stylesheet" href="./static/group_view.css">
    <link rel="stylesheet" href="./static/hero_section.css">
    <link rel="stylesheet" href="./static/loading_animation.css">
    <link rel="stylesheet" href="./static/login_page.css">
    <link rel="stylesheet" href="./static/modern_footer.css">
    <link rel="stylesheet" href="./static/modern_navbar.css">
    <link rel="stylesheet" href="./static/post_card.css">
    <link rel="stylesheet" href="./static/profile_pages.css">
    <link rel="stylesheet" href="./static/responsive_adjustments.css">
    <link rel="stylesheet" href="./static/utility_classes.css">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" />
    <script src="./static/index.js" defer></script>
</head>
<style>
    .payment-container {
        max-width: 800px;
        margin: 3rem auto;
        padding: 0 1.5rem;
    }
    
    .payment-steps {
        display: flex;
        justify-content: center;
        margin-bottom: 3rem;
        gap: 2rem;
    }
    
    .payment-step {
        display: flex;
        flex-direction: column;
        align-items: center;
    }
    
    .payment-step.active .step-number {
        background: var(--accent-600);
        color: white;
    }
    
    .payment-step.completed .step-number {
        background: var(--success);
        color: white;
    }
    
    .step-number {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        background: var(--border-color);
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        margin-bottom: 0.5rem;
    }
    
    .payment-header {
        text-align: center;
        margin-bottom: 3rem;
    }
    
    .payment-header h1 {
        color: var(--primary-700);
        font-size: 2.5rem;
        margin-bottom: 1rem;
    }
    
    .payment-card {
        background: var(--surface);
        border-radius: var(--border-radius-lg);
        padding: 2.5rem;
        box-shadow: var(--shadow-xl);
        border: 1px solid var(--border-color);
        margin-bottom: 2rem;
    }
    
    .payment-summary {
        background: var(--primary-100);
        border-radius: var(--border-radius-md);
        padding: 1.5rem;
        margin-bottom: 2rem;
        border-left: 4px solid var(--accent-600);
    }
    
    .payment-form .form-group {
        margin-bottom: 1.5rem;
    }
    
    .card-input-group {
        position: relative;
    }
    
    .card-icon {
        position: absolute;
        right: 1rem;
        top: 50%;
        transform: translateY(-50%);
        color: var(--text-light);
    }
    
    .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1rem;
    }
    
    .payment-actions {
        display: flex;
        justify-content: space-between;
        margin-top: 3rem;
        padding-top: 2rem;
        border-top: 1px solid var(--border-color);
    }
    
    .demo-notice {
        background: var(--warning);
        color: white;
        padding: 1rem;
        border-radius: var(--border-radius-md);
        text-align: center;
        margin-bottom: 2rem;
        font-weight: bold;
    }
    /* ===============================
    FORM LABELS & INPUTS
    =============================== */
    .form-label {
        display: block;
        margin-bottom: 0.75rem;
        font-weight: 600;
        color: var(--text-color);
        font-size: 1.1rem;
    }

    .form-label.required::after {
        content: " *";
        color: var(--danger);
    }

    .form-control {
        width: 100%;
        padding: 1.125rem 1.25rem;
        border: 2px solid var(--border-color);
        border-radius: var(--border-radius-md);
        font-size: 1.125rem;
        transition: all var(--transition-fast);
        background: var(--background);
        color: var(--text-color);
        font-family: inherit;
    }

    .form-control:focus {
        outline: none;
        border-color: var(--accent-600);
        background: var(--surface);
        box-shadow: 0 0 0 3px rgba(255, 107, 53, 0.1);
    }

    .form-control::placeholder {
        color: var(--text-light);
        opacity: 0.7;
    }

    .help-text {
        display: block;
        margin-top: 0.5rem;
        font-size: 0.9rem;
        color: var(--text-light);
        font-style: italic;
    }

    /* ===============================
    BUTTON STYLES
    =============================== */
    .btn-back {
        padding: 1rem 2rem;
        background: var(--surface);
        color: var(--primary-700);
        border: 2px solid var(--border-color);
        border-radius: var(--border-radius-md);
        font-weight: 600;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        transition: all var(--transition-fast);
        cursor: pointer;
    }

    .btn-back:hover {
        background: var(--primary-100);
        border-color: var(--primary-300);
        transform: translateY(-2px);
    }

    .btn-checkout {
        padding: 1rem 3rem;
        background: linear-gradient(45deg, var(--accent-600), var(--accent-400));
        color: white;
        border: none;
        border-radius: var(--border-radius-md);
        font-weight: 600;
        font-size: 1.125rem;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        transition: all var(--transition-normal);
    }

    .btn-checkout:hover:not(:disabled) {
        transform: translateY(-3px);
        box-shadow: var(--shadow-lg);
    }

    .btn-checkout:disabled {
        opacity: 0.6;
        cursor: not-allowed;
    }

    .btn-checkout i.fa-spinner {
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        to { transform: rotate(360deg); }
    }

    /* ===============================
    CARD SPECIFIC STYLES
    =============================== */
    .card-input-group .form-control {
        padding-right: 3.5rem; /* Make space for the card icon */
    }

    /* ===============================
    RESPONSIVE ADJUSTMENTS
    =============================== */
    @media (max-width: 768px) {
        .form-row {
            grid-template-columns: 1fr;
        }
        
        .payment-actions {
            flex-direction: column;
            gap: 1rem;
        }
        
        .btn-back, .btn-checkout {
            width: 100%;
            justify-content: center;
        }
        
        .payment-card {
            padding: 2rem 1.5rem;
        }
    }
</style>

<body>
<?php include './views/navbar.php'; ?>

<section class="section-padding">
    <div class="payment-container">
        <!-- Payment Steps -->
        <div class="payment-steps">
            <div class="payment-step completed">
                <div class="step-number"><i class="fas fa-check"></i></div>
                <div class="step-label">Kosár</div>
            </div>
            <div class="payment-step completed">
                <div class="step-number"><i class="fas fa-check"></i></div>
                <div class="step-label">Szállítás</div>
            </div>
            <div class="payment-step active">
                <div class="step-number">3</div>
                <div class="step-label">Fizetés</div>
            </div>
            <div class="payment-step">
                <div class="step-number">4</div>
                <div class="step-label">Megerősítés</div>
            </div>
        </div>
        
        <!-- Header -->
        <div class="payment-header">
            <h1>Fizetés</h1>
            <p>Kérjük, add meg bankkártya adataidat</p>
        </div>
        
        <!-- Demo Notice -->
        <div class="demo-notice">
            ⚠️ DEMÓ - Nincs valódi fizetés, tesztadatokat használj!
        </div>
        
        <!-- Payment Summary -->
        <div class="payment-summary">
            <h3>Fizetendő összeg: <strong><?php echo number_format($total_amount, 0, ',', ' '); ?> Ft</strong></h3>
            <p>Rendelés száma: <?php echo $order_number; ?></p>
        </div>
        
        <!-- Payment Form -->
        <div class="payment-card">
            <h2 style="margin-bottom: 2rem; color: var(--primary-700);">Bankkártya adatok</h2>
            
            <form id="payment-form" class="payment-form">
                <div class="form-group">
                    <label class="form-label">Kártyatulajdonos neve</label>
                    <input type="text" class="form-control" placeholder="Minta János" value="Minta János" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Kártyaszám</label>
                    <div class="card-input-group">
                        <input type="text" class="form-control" placeholder="1234 5678 9012 3456" 
                               value="4111 1111 1111 1111" required pattern="[\d\s]{16,19}">
                        <div class="card-icon">
                            <i class="fab fa-cc-visa"></i>
                        </div>
                    </div>
                    <small class="help-text">Teszt kártyaszám: 4111 1111 1111 1111</small>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Lejárati dátum</label>
                        <input type="text" class="form-control" placeholder="HH/ÉÉ" value="12/25" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">CVC kód</label>
                        <input type="text" class="form-control" placeholder="123" value="123" required>
                    </div>
                </div>
                
                <div class="payment-actions">
                    <a href="checkout.php" class="btn-back">
                        <i class="fas fa-arrow-left"></i>
                        Vissza
                    </a>
                    <button type="submit" class="btn-checkout">
                        <i class="fas fa-lock"></i>
                        Fizetés elküldése
                    </button>
                </div>
            </form>
        </div>
        
        <!-- Security Info -->
        <div style="text-align: center; color: var(--text-light); margin-top: 2rem;">
            <p><i class="fas fa-shield-alt"></i> Biztonságos fizetés • Adataid védve vannak</p>
        </div>
    </div>
</section>
    <script>
        document.getElementById('payment-form').addEventListener('submit', function(e) {
            e.preventDefault();
            // Simulate payment processing
            const submitBtn = this.querySelector('.btn-checkout');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Fizetés feldolgozása...';
            submitBtn.disabled = true;
            
            setTimeout(() => {
                // Redirect to confirmation page
                window.location.href = 'order-confirmation.php?order=<?php echo $order_number; ?>';
            }, 2000);
        });
    </script>
</body>
</html>