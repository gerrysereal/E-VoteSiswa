<?php
defined('BASEPATH') OR exit('No direct script access allowed');

if ( ! function_exists('securityheaders_post_controller')) {
	function securityheaders_post_controller() {
		$CI =& get_instance();

		if ($CI->config->item('security_headers') !== TRUE) {
			return;
		}

		$html = (string) $CI->output->get_output();
		if ($html !== '' && strpos($CI->output->get_content_type(), 'text/html') !== FALSE) {
			$CI->output->set_output(securityheaders_apply_csp($html));
		}

		$CI->output->set_header('X-Content-Type-Options: nosniff');
		$CI->output->set_header('X-Frame-Options: DENY');
		$CI->output->set_header('Referrer-Policy: strict-origin-when-cross-origin');
		$CI->output->set_header('Permissions-Policy: accelerometer=(), autoplay=(), camera=(), display-capture=(), encrypted-media=(), fullscreen=(self), geolocation=(), gyroscope=(), magnetometer=(), microphone=(), midi=(), payment=(), usb=(), xr-spatial-tracking=()');
		$CI->output->set_header('Cross-Origin-Opener-Policy: same-origin');
		$CI->output->set_header('Cross-Origin-Resource-Policy: same-origin');
		$CI->output->set_header('Cross-Origin-Embedder-Policy: credentialless');
		$CI->output->set_header('Content-Security-Policy: ' . securityheaders_policy());

		if (securityheaders_is_https()) {
			$CI->output->set_header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
		}
	}
}

if ( ! function_exists('securityheaders_is_https')) {
	function securityheaders_is_https() {
		if (is_https()) {
			return TRUE;
		}
		return isset($_SERVER['HTTP_X_FORWARDED_PROTO'])
			&& strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) === 'https';
	}
}

if ( ! function_exists('securityheaders_policy')) {
        function securityheaders_policy() {
                return "default-src 'self'; "
                        . "script-src 'self' 'unsafe-eval' 'unsafe-inline' 'nonce-" . securityheaders_nonce() . "' https://cdn.jsdelivr.net; "
                        . "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; "
                        . "font-src 'self' https://fonts.gstatic.com; "
                        . "img-src 'self' data:; "
                        . "connect-src 'self'; "
                        . "object-src 'none'; "
                        . "base-uri 'self'; "
                        . "form-action 'self'; "
                        . "frame-ancestors 'none';";
        }
}

if ( ! function_exists('securityheaders_nonce')) {
	function securityheaders_nonce() {
		static $nonce = NULL;
		if ($nonce === NULL) {
			$nonce = base64_encode(random_bytes(16));
		}
		return $nonce;
	}
}

if ( ! function_exists('securityheaders_apply_csp')) {
	function securityheaders_apply_csp($html) {
		if (stripos($html, '<script') === FALSE && stripos($html, '<style>') === FALSE) {
			return $html;
		}

		$nonce = securityheaders_nonce();

		$html = preg_replace_callback(
			'#<script(?![^>]*\bnonce=)(?![^>]*\bsrc=)#i',
			function () use ($nonce) { return '<script nonce="' . $nonce . '"'; },
			$html
		);

		$html = preg_replace_callback(
			'#<style(?![^>]*\bnonce=)(?![^>]*\btype=)#i',
			function () use ($nonce) { return '<style nonce="' . $nonce . '"'; },
			$html
		);

		return $html;
	}
}
