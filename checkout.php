<?php
session_start();
include_once __DIR__ . '/app/db.php';
// Szimulált kosár adatok a frontend teszteléshez
$cart_items = [
    [
        'product_id' => 1,
        'name' => 'Tech Laptop',
        'price' => 120000,
        'quantity' => 1,
        'image' => 'laptop.jpg'
    ],
    [
        'product_id' => 2,
        'name' => 'Wireless Headphones',
        'price' => 45000,
        'quantity' => 2,
        'image' => 'headphones.jpg'
    ],
    [
        'product_id' => 3,
        'name' => 'Smart Watch',
        'price' => 75000,
        'quantity' => 1,
        'image' => 'watch.jpg'
    ]
];

// Szállítási költség és összesítések
$shipping_cost = 1990;
$subtotal = array_sum(array_map(function($item) {
    return $item['price'] * $item['quantity'];
}, $cart_items));
$total = $subtotal + $shipping_cost;

// Felhasználó adatok (ha be van jelentkezve)
$user_data = isset($_SESSION['user_id']) ? [
    'full_name' => 'Kovács János',
    'email' => 'kovacs.janos@email.com',
    'phone' => '+36 30 123 4567'
] : null;
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
        /* ===============================
          CHECKOUT PAGE STYLES
        =============================== */
        .checkout-container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 1.5rem;
        }

        .checkout-header {
            text-align: center;
            margin-bottom: 3rem;
            padding-bottom: 1.5rem;
            border-bottom: 2px solid var(--border-color);
        }

        .checkout-header h1 {
            font-size: 2.5rem;
            color: var(--primary-700);
            margin-bottom: 0.5rem;
        }

        .checkout-header p {
            color: var(--text-light);
            font-size: 1.1rem;
        }

        .checkout-steps {
            display: flex;
            justify-content: center;
            margin-bottom: 3rem;
            gap: 2rem;
        }

        .checkout-step {
            display: flex;
            flex-direction: column;
            align-items: center;
            position: relative;
        }

        .checkout-step.active .step-number {
            background: var(--accent-600);
            color: white;
            border-color: var(--accent-600);
        }

        .checkout-step.completed .step-number {
            background: var(--success);
            color: white;
            border-color: var(--success);
        }

        .step-number {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: var(--surface);
            border: 3px solid var(--border-color);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 1.2rem;
            margin-bottom: 0.5rem;
            transition: all var(--transition-normal);
        }

        .step-label {
            color: var(--text-light);
            font-weight: 500;
            font-size: 0.95rem;
        }

        .checkout-step.active .step-label {
            color: var(--accent-600);
            font-weight: 600;
        }

        .checkout-content {
            display: grid;
            grid-template-columns: 1.5fr 1fr;
            gap: 3rem;
        }

        @media (max-width: 992px) {
            .checkout-content {
                grid-template-columns: 1fr;
            }
        }

        /* ===============================
          ADDRESS FORMS
        =============================== */
        .checkout-section {
            background: var(--surface);
            border-radius: var(--border-radius-lg);
            padding: 2rem;
            box-shadow: var(--shadow-md);
            border: 1px solid var(--border-color);
            margin-bottom: 2rem;
        }

        .section-header {
            display: flex;
            align-items: center;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid var(--border-color);
        }

        .section-header i {
            font-size: 1.5rem;
            color: var(--accent-600);
            margin-right: 1rem;
            width: 40px;
            height: 40px;
            background: var(--accent-200);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .section-header h2 {
            font-size: 1.5rem;
            color: var(--primary-700);
            margin: 0;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1.5rem;
            margin-bottom: 1.5rem;
        }

        @media (max-width: 768px) {
            .form-grid {
                grid-template-columns: 1fr;
            }
        }

        .form-group.full-width {
            grid-column: 1 / -1;
        }

        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: var(--text-color);
        }

        .form-label.required::after {
            content: " *";
            color: var(--danger);
        }

        .form-control {
            width: 100%;
            padding: 1rem;
            border: 2px solid var(--border-color);
            border-radius: var(--border-radius-md);
            font-size: 1rem;
            background: var(--background);
            color: var(--text-color);
            transition: all var(--transition-fast);
        }

        .form-control:focus {
            outline: none;
            border-color: var(--accent-600);
            box-shadow: 0 0 0 3px rgba(255, 107, 53, 0.1);
        }

        .form-check {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin: 1rem 0;
        }

        .form-check-input {
            width: 20px;
            height: 20px;
            border: 2px solid var(--border-color);
            border-radius: 6px;
            cursor: pointer;
        }

        .form-check-label {
            font-weight: 500;
            color: var(--text-color);
            cursor: pointer;
        }

        .same-as-shipping {
            margin-top: 1.5rem;
            padding: 1.5rem;
            background: var(--primary-100);
            border-radius: var(--border-radius-md);
            border-left: 4px solid var(--accent-600);
        }

        /* ===============================
          ORDER SUMMARY
        =============================== */
        .order-summary {
            position: sticky;
            top: 2rem;
        }

        .summary-items {
            margin-bottom: 1.5rem;
        }

        .summary-item {
            display: flex;
            align-items: center;
            padding: 1rem;
            background: var(--background);
            border-radius: var(--border-radius-md);
            margin-bottom: 0.75rem;
        }

        .summary-item img {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: var(--border-radius-sm);
            margin-right: 1rem;
        }

        .item-details {
            flex: 1;
        }

        .item-name {
            font-weight: 600;
            color: var(--primary-700);
            margin-bottom: 0.25rem;
        }

        .item-price {
            color: var(--accent-600);
            font-weight: 600;
        }

        .item-quantity {
            color: var(--text-light);
            font-size: 0.9rem;
        }

        .summary-totals {
            background: var(--surface);
            border-radius: var(--border-radius-lg);
            padding: 1.5rem;
            box-shadow: var(--shadow-md);
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            padding: 0.75rem 0;
            border-bottom: 1px solid var(--border-color);
        }

        .total-row:last-child {
            border-bottom: none;
            font-weight: bold;
            font-size: 1.2rem;
            color: var(--primary-700);
        }

        .total-row.total {
            border-top: 2px solid var(--accent-600);
            padding-top: 1rem;
            margin-top: 0.5rem;
        }

        .total-label {
            color: var(--text-color);
        }

        .total-value {
            color: var(--accent-600);
            font-weight: 600;
        }

        /* ===============================
          PAYMENT METHOD
        =============================== */
        .payment-methods {
            margin-top: 1rem;
        }

        .payment-option {
            display: flex;
            align-items: center;
            padding: 1rem;
            border: 2px solid var(--border-color);
            border-radius: var(--border-radius-md);
            margin-bottom: 1rem;
            cursor: pointer;
            transition: all var(--transition-fast);
        }

        .payment-option:hover {
            border-color: var(--accent-400);
        }

        .payment-option.selected {
            border-color: var(--accent-600);
            background: var(--accent-200);
        }

        .payment-icon {
            width: 50px;
            height: 50px;
            background: var(--primary-100);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 1rem;
            color: var(--primary-500);
            font-size: 1.5rem;
        }

        .payment-details h3 {
            margin: 0 0 0.25rem 0;
            color: var(--primary-700);
        }

        .payment-details p {
            margin: 0;
            color: var(--text-light);
            font-size: 0.9rem;
        }

        /* ===============================
          ORDER COMMENT
        =============================== */
        .order-comment textarea {
            min-height: 120px;
            resize: vertical;
        }

        /* ===============================
          CHECKOUT ACTIONS
        =============================== */
        .checkout-actions {
            display: flex;
            justify-content: space-between;
            margin-top: 3rem;
            padding-top: 2rem;
            border-top: 2px solid var(--border-color);
        }

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
        }

        .btn-back:hover {
            background: var(--primary-100);
            border-color: var(--primary-300);
        }

        .btn-checkout {
            padding: 1rem 3rem;
            background: linear-gradient(45deg, var(--accent-600), var(--accent-400));
            color: white;
            border: none;
            border-radius: var(--border-radius-md);
            font-weight: 600;
            font-size: 1.1rem;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all var(--transition-normal);
        }

        .btn-checkout:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }

        .btn-checkout:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        /* ===============================
          SECURITY BADGE
        =============================== */
        .security-badge {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 1rem;
            margin-top: 2rem;
            padding: 1rem;
            background: var(--primary-100);
            border-radius: var(--border-radius-md);
        }

        .security-badge i {
            color: var(--success);
            font-size: 1.5rem;
        }

        .security-text {
            color: var(--text-light);
            font-size: 0.9rem;
        }

        /* ===============================
          LOADING OVERLAY
        =============================== */
        .loading-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255, 255, 255, 0.9);
            z-index: 1000;
            align-items: center;
            justify-content: center;
            flex-direction: column;
        }

        .loading-overlay.active {
            display: flex;
        }

        .loading-spinner {
            width: 50px;
            height: 50px;
            border: 5px solid var(--border-color);
            border-top-color: var(--accent-600);
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin-bottom: 1rem;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        /* ===============================
          RESPONSIVE
        =============================== */
        @media (max-width: 768px) {
            .checkout-steps {
                flex-direction: column;
                align-items: center;
                gap: 1.5rem;
            }
            
            .checkout-step {
                flex-direction: row;
                gap: 1rem;
            }
            
            .step-number {
                margin-bottom: 0;
            }
            
            .checkout-actions {
                flex-direction: column;
                gap: 1rem;
            }
            
            .btn-back, .btn-checkout {
                width: 100%;
                justify-content: center;
            }
        }
</style>

<body>
<?php include './views/navbar.php'; ?>

<section class="section-padding">
    <div class="checkout-container">
        <!-- Checkout Steps -->
        <div class="checkout-steps">
            <div class="checkout-step completed">
            <div class="step-number"><i class="fas fa-check"></i></div>
                <div class="step-label">Kosár</div>
            </div>
            <div class="checkout-step active">
                <div class="step-number">2</div>
                <div class="step-label">Szállítás</div>
            </div>
            <div class="checkout-step">
                <div class="step-number">3</div>
                <div class="step-label">Fizetés</div>
            </div>
            <div class="checkout-step">
                <div class="step-number">4</div>
                <div class="step-label">Megerősítés</div>
            </div>
        </div>

        <!-- Header -->
        <div class="checkout-header">
            <h1>Fizetés</h1>
            <p>Kérjük, ellenőrizd megrendelésed adatait</p>
        </div>

        <div class="checkout-content">
            <!-- Left Column: Forms -->
            <div class="checkout-left">
                <!-- Shipping Address -->
                <div class="checkout-section">
                    <div class="section-header">
                        <i class="fas fa-truck"></i>
                        <h2>Szállítási cím</h2>
                    </div>
                    
                    <form id="shipping-form">
                        <div class="form-grid">
                            <div class="form-group full-width">
                                <label for="full_name" class="form-label required">Teljes név</label>
                                <input type="text" id="full_name" name="full_name" class="form-control" 
                                       value="<?php echo $user_data['full_name'] ?? ''; ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="country" class="form-label required">Ország</label>
                                <select id="country" name="country" class="form-control" required>
                                    <option value="">Válassz országot</option>
                                    <option value="HU" selected>Magyarország</option>
                                    <option value="SK">Szlovákia</option>
                                    <option value="RO">Románia</option>
                                    <option value="AT">Ausztria</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label for="zip_code" class="form-label required">Irányítószám</label>
                                <input type="text" id="zip_code" name="zip_code" class="form-control" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="city" class="form-label required">Város</label>
                                <input type="text" id="city" name="city" class="form-control" required>
                            </div>
                            
                            <div class="form-group full-width">
                                <label for="street_address" class="form-label required">Utca, házszám</label>
                                <input type="text" id="street_address" name="street_address" class="form-control" 
                                       placeholder="Pl.: Példa utca 12." required>
                            </div>
                            
                            <div class="form-group">
                                <label for="phone" class="form-label required">Telefonszám</label>
                                <input type="tel" id="phone" name="phone" class="form-control" 
                                       value="<?php echo $user_data['phone'] ?? ''; ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="email" class="form-label required">Email cím</label>
                                <input type="email" id="email" name="email" class="form-control" 
                                       value="<?php echo $user_data['email'] ?? ''; ?>" required>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Billing Address -->
                <div class="checkout-section">
                    <div class="section-header">
                        <i class="fas fa-file-invoice-dollar"></i>
                        <h2>Számlázási cím</h2>
                    </div>
                    
                    <div class="same-as-shipping">
                        <div class="form-check">
                            <input type="checkbox" id="same-as-shipping" class="form-check-input" checked>
                            <label for="same-as-shipping" class="form-check-label">
                                A számlázási cím megegyezik a szállítási címmel
                            </label>
                        </div>
                    </div>
                    
                    <form id="billing-form" style="display: none;">
                        <div class="form-grid">
                            <div class="form-group full-width">
                                <label for="billing_full_name" class="form-label required">Teljes név</label>
                                <input type="text" id="billing_full_name" name="billing_full_name" class="form-control">
                            </div>
                            
                            <div class="form-group">
                                <label for="billing_country" class="form-label required">Ország</label>
                                <select id="billing_country" name="billing_country" class="form-control">
                                    <option value="">Válassz országot</option>
                                    <option value="HU">Magyarország</option>
                                    <option value="SK">Szlovákia</option>
                                    <option value="RO">Románia</option>
                                    <option value="AT">Ausztria</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label for="billing_zip_code" class="form-label required">Irányítószám</label>
                                <input type="text" id="billing_zip_code" name="billing_zip_code" class="form-control">
                            </div>
                            
                            <div class="form-group">
                                <label for="billing_city" class="form-label required">Város</label>
                                <input type="text" id="billing_city" name="billing_city" class="form-control">
                            </div>
                            
                            <div class="form-group full-width">
                                <label for="billing_street_address" class="form-label required">Utca, házszám</label>
                                <input type="text" id="billing_street_address" name="billing_street_address" 
                                       class="form-control" placeholder="Pl.: Példa utca 12.">
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Payment Method -->
                <div class="checkout-section">
                    <div class="section-header">
                        <i class="fas fa-credit-card"></i>
                        <h2>Fizetési mód</h2>
                    </div>
                    
                    <div class="payment-methods">
                        <div class="payment-option selected" data-method="card">
                            <div class="payment-icon">
                                <i class="fas fa-credit-card"></i>
                            </div>
                            <div class="payment-details">
                                <h3>Bankkártya</h3>
                                <p>Azonnali fizetés bankkártyával</p>
                            </div>
                        </div>
                        <!-- További fizetési módok lehetnek itt -->
                    </div>
                </div>

                <!-- Order Comment -->
                <div class="checkout-section">
                    <div class="section-header">
                        <i class="fas fa-comment-alt"></i>
                        <h2>Megjegyzés a rendeléshez (opcionális)</h2>
                    </div>
                    
                    <div class="order-comment">
                        <textarea id="order_comment" name="order_comment" class="form-control" 
                                  placeholder="Például: ajtócsengő kód, speciális kérés..."></textarea>
                    </div>
                </div>
            </div>

            <!-- Right Column: Order Summary -->
            <div class="order-summary">
                <div class="checkout-section">
                    <div class="section-header">
                        <i class="fas fa-shopping-bag"></i>
                        <h2>Rendelés összegzése</h2>
                    </div>
                    
                    <div class="summary-items">
                        <?php foreach ($cart_items as $item): ?>
                        <div class="summary-item">
                            <div class="item-image">
                                <!-- <img src="images/<?php echo $item['image']; ?>" alt="<?php echo $item['name']; ?>"> -->
                                <div style="width: 60px; height: 60px; background: var(--primary-100); border-radius: var(--border-radius-sm); display: flex; align-items: center; justify-content: center; color: var(--primary-500);">
                                    <i class="fas fa-box"></i>
                                </div>
                            </div>
                            <div class="item-details">
                                <div class="item-name"><?php echo $item['name']; ?></div>
                                <div class="item-price"><?php echo number_format($item['price'], 0, ',', ' '); ?> Ft</div>
                                <div class="item-quantity">Darab: <?php echo $item['quantity']; ?></div>
                            </div>
                            <div class="item-total">
                                <strong><?php echo number_format($item['price'] * $item['quantity'], 0, ',', ' '); ?> Ft</strong>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <div class="summary-totals">
                        <div class="total-row">
                            <span class="total-label">Részösszeg</span>
                            <span class="total-value"><?php echo number_format($subtotal, 0, ',', ' '); ?> Ft</span>
                        </div>
                        <div class="total-row">
                            <span class="total-label">Szállítás</span>
                            <span class="total-value"><?php echo number_format($shipping_cost, 0, ',', ' '); ?> Ft</span>
                        </div>
                        <div class="total-row total">
                            <span class="total-label">Végösszeg</span>
                            <span class="total-value"><?php echo number_format($total, 0, ',', ' '); ?> Ft</span>
                        </div>
                    </div>
                </div>

                <!-- Security Badge -->
                <div class="security-badge">
                    <a href="payment.php">
                    <i class="fas fa-lock"></i>
                    <div class="security-text">
                        Biztonságos fizetés • Adatvédelem garantálva
                    </div>
                    </a>
                </div>
            </div>
        </div>

        <!-- Checkout Actions -->
        <div class="checkout-actions">
            <a href="cart.php" class="btn-back">
                <i class="fas fa-arrow-left"></i>
                Vissza a kosárhoz
            </a>
            <button id="complete-order" class="btn-checkout">
                <i class="fas fa-lock"></i>
                Biztonságos fizetés
            </button>
        </div>
    </div>

    <!-- Loading Overlay -->
    <div class="loading-overlay" id="loading-overlay">
        <div class="loading-spinner"></div>
        <p>Rendelés feldolgozása...</p>
    </div>
</section>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Same as shipping checkbox
            const sameAsShipping = document.getElementById('same-as-shipping');
            const billingForm = document.getElementById('billing-form');
            
            sameAsShipping.addEventListener('change', function() {
                if (this.checked) {
                    billingForm.style.display = 'none';
                } else {
                    billingForm.style.display = 'block';
                }
            });

            // Payment method selection
            const paymentOptions = document.querySelectorAll('.payment-option');
            paymentOptions.forEach(option => {
                option.addEventListener('click', function() {
                    paymentOptions.forEach(opt => opt.classList.remove('selected'));
                    this.classList.add('selected');
                });
            });

            // Form validation and submission
            const completeOrderBtn = document.getElementById('complete-order');
            const loadingOverlay = document.getElementById('loading-overlay');
            
            completeOrderBtn.addEventListener('click', function() {
                // Basic form validation
                const shippingForm = document.getElementById('shipping-form');
                let isValid = true;
                
                // Check required fields in shipping form
                const requiredFields = shippingForm.querySelectorAll('[required]');
                requiredFields.forEach(field => {
                    if (!field.value.trim()) {
                        isValid = false;
                        field.style.borderColor = 'var(--danger)';
                    } else {
                        field.style.borderColor = '';
                    }
                });
                
                // If billing form is visible, check those too
                if (!sameAsShipping.checked) {
                    const billingRequired = billingForm.querySelectorAll('[required]');
                    billingRequired.forEach(field => {
                        if (!field.value.trim()) {
                            isValid = false;
                            field.style.borderColor = 'var(--danger)';
                        } else {
                            field.style.borderColor = '';
                        }
                    });
                }
                
                if (!isValid) {
                    alert('Kérjük, töltsd ki az összes kötelező mezőt!');
                    return;
                }
                
                // Show loading overlay
                loadingOverlay.classList.add('active');
                
                // Simulate API call delay
                setTimeout(() => {
                    // In a real app, you would send the data to the server here
                    console.log('Order submitted');
                    
                    // Collect form data
                    const formData = {
                        shipping: {
                            full_name: document.getElementById('full_name').value,
                            country: document.getElementById('country').value,
                            zip_code: document.getElementById('zip_code').value,
                            city: document.getElementById('city').value,
                            street_address: document.getElementById('street_address').value,
                            phone: document.getElementById('phone').value,
                            email: document.getElementById('email').value
                        },
                        billing: sameAsShipping.checked ? 'same_as_shipping' : {
                            full_name: document.getElementById('billing_full_name').value,
                            country: document.getElementById('billing_country').value,
                            zip_code: document.getElementById('billing_zip_code').value,
                            city: document.getElementById('billing_city').value,
                            street_address: document.getElementById('billing_street_address').value
                        },
                        payment_method: document.querySelector('.payment-option.selected').dataset.method,
                        order_comment: document.getElementById('order_comment').value,
                        total: <?php echo $total; ?>,
                        shipping_cost: <?php echo $shipping_cost; ?>
                    };
                    
                    console.log('Form data:', formData);
                    
                    // Redirect to success page (in real app)
                    window.location.href = 'payment.php';
                    
                    // For demo purposes, just show a success message
                    loadingOverlay.classList.remove('active');
                    alert('Sikeres rendelés! Az oldal átirányít a fizetéshez...');
                    
                }, 2000);
            });

            // Auto-fill city based on zip code (demo functionality)
            const zipCodeInput = document.getElementById('zip_code');
            const cityInput = document.getElementById('city');
            
            zipCodeInput.addEventListener('blur', function() {
                if (this.value.length === 4 && !cityInput.value) {
                    // Demo: Simulate API call to get city from zip code
                    const zipToCity = {
                        '1011': 'Budapest',
                        '6720': 'Szeged',
                        '7621': 'Pécs',
                        '4025': 'Debrecen',
                        '3300': 'Eger'
                    };
                    
                    if (zipToCity[this.value]) {
                        cityInput.value = zipToCity[this.value];
                    }
                }
            });
        });
    </script>
</body>
</html>