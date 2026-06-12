<footer class="text-white mt-5 pt-5 pb-4" style="background: var(--vl-gradient-dark);">
    <div class="container">
        <div class="row g-4">
            <div class="col-md-4">
                <h5 class="fw-bold mb-3 d-flex align-items-center gap-2 text-white">
                    <span class="d-inline-flex align-items-center justify-content-center rounded-3"
                          style="width:32px;height:32px;background:rgba(255,255,255,.14);">
                        <i class="bi bi-send-fill" style="font-size:.85rem;transform:rotate(45deg)"></i>
                    </span>
                    Volamani
                </h5>
                <p class="text-white-50 small" style="max-width:320px;">Africa's complete digital business ecosystem. Sell products, offer services, and grow your business online — all from one dashboard.</p>
                <div class="d-flex gap-2 mt-3">
                    @foreach(['bi-twitter-x','bi-instagram','bi-linkedin','bi-whatsapp'] as $soc)
                        <a href="#" class="d-inline-flex align-items-center justify-content-center text-white rounded-circle"
                           style="width:38px;height:38px;background:rgba(255,255,255,.1);transition:background .15s;"
                           onmouseover="this.style.background='rgba(255,255,255,.22)'" onmouseout="this.style.background='rgba(255,255,255,.1)'">
                            <i class="bi {{ $soc }}"></i>
                        </a>
                    @endforeach
                </div>
            </div>
            <div class="col-md-2">
                <h6 class="fw-bold mb-3 text-white text-uppercase" style="font-size:.78rem;letter-spacing:.06em;">Marketplace</h6>
                <ul class="list-unstyled small">
                    <li class="mb-2"><a href="{{ route('marketplace.products.index') }}" class="footer-link text-decoration-none">Digital Products</a></li>
                    <li class="mb-2"><a href="{{ route('marketplace.services.index') }}" class="footer-link text-decoration-none">Services</a></li>
                    <li class="mb-2"><a href="{{ route('marketplace.consultants.index') }}" class="footer-link text-decoration-none">Consultants</a></li>
                    <li class="mb-2"><a href="{{ route('marketplace.requests.index') }}" class="footer-link text-decoration-none">Post a Request</a></li>
                </ul>
            </div>
            <div class="col-md-2">
                <h6 class="fw-bold mb-3 text-white text-uppercase" style="font-size:.78rem;letter-spacing:.06em;">Sellers</h6>
                <ul class="list-unstyled small">
                    <li class="mb-2"><a href="{{ route('register') }}" class="footer-link text-decoration-none">Start Selling</a></li>
                    <li class="mb-2"><a href="{{ route('vendor.onboarding') }}" class="footer-link text-decoration-none">Vendor Setup</a></li>
                    <li class="mb-2"><a href="#" class="footer-link text-decoration-none">Pricing Plans</a></li>
                    <li class="mb-2"><a href="#" class="footer-link text-decoration-none">Seller Guide</a></li>
                </ul>
            </div>
            <div class="col-md-2">
                <h6 class="fw-bold mb-3 text-white text-uppercase" style="font-size:.78rem;letter-spacing:.06em;">Support</h6>
                <ul class="list-unstyled small">
                    <li class="mb-2"><a href="#" class="footer-link text-decoration-none">Help Center</a></li>
                    <li class="mb-2"><a href="#" class="footer-link text-decoration-none">Dispute Policy</a></li>
                    <li class="mb-2"><a href="#" class="footer-link text-decoration-none">Refund Policy</a></li>
                    <li class="mb-2"><a href="#" class="footer-link text-decoration-none">Contact Us</a></li>
                </ul>
            </div>
            <div class="col-md-2">
                <h6 class="fw-bold mb-3 text-white text-uppercase" style="font-size:.78rem;letter-spacing:.06em;">Legal</h6>
                <ul class="list-unstyled small">
                    <li class="mb-2"><a href="#" class="footer-link text-decoration-none">Privacy Policy</a></li>
                    <li class="mb-2"><a href="#" class="footer-link text-decoration-none">Terms of Service</a></li>
                    <li class="mb-2"><a href="#" class="footer-link text-decoration-none">Cookie Policy</a></li>
                </ul>
            </div>
        </div>
        <hr class="my-4" style="border-color:rgba(255,255,255,.12);">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 small text-white-50">
            <span>&copy; {{ date('Y') }} Volamani. All rights reserved.</span>
            <span class="d-flex align-items-center gap-3">
                <span><i class="bi bi-shield-check me-1"></i>Escrow protected</span>
                <span>Made with <i class="bi bi-heart-fill text-danger"></i> for African entrepreneurs</span>
            </span>
        </div>
    </div>
</footer>

<style>
    footer .footer-link { color: rgba(255,255,255,.62); transition: color .15s, padding-left .15s; }
    footer .footer-link:hover { color: #fff; padding-left: 3px; }
</style>
