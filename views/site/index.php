<?php

/* @var $this yii\web\View */

$this->title = 'My Yii Application';
?>

<style>
    .hero-section {
        height: 90vh;
        background: linear-gradient(135deg, #6a11cb, #2575fc);
        padding: 60px 20px;
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        text-align: center;
    }

    .hero-section h1 {
        font-size: 3.5rem;
        font-weight: 700;
        text-shadow: 2px 2px 6px rgba(0, 0, 0, 0.3);
    }

    .hero-section p {
        font-size: 1.25rem;
        margin-bottom: 30px;
        opacity: 0.95;
    }

    .hero-section .btn {
        font-size: 1.1rem;
        padding: 12px 30px;
        border-radius: 50px;
        transition: all 0.3s ease-in-out;
    }

    .hero-section .btn:hover {
        background-color: white;
        color: #2575fc;
        transform: translateY(-2px);
        box-shadow: 0 6px 15px rgba(0, 0, 0, 0.2);
    }

    footer {
        background-color: #f8f9fa;
        color: #6c757d;
    }
</style>

<div class="site-index">

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <h1 class="display-3">Welcome to Estetica</h1>
          
        </div>
    </section>

    <!-- Footer -->
    <footer class="text-center py-4 border-top">
        <small>© My Company <?= date('Y') ?></small>
    </footer>

</div>