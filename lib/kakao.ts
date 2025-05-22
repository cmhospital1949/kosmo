import axios from 'axios';

const KAKAOPAY_API_URL = {
  READY: 'https://kapi.kakao.com/v1/payment/ready',
  APPROVE: 'https://kapi.kakao.com/v1/payment/approve',
  ORDER: 'https://kapi.kakao.com/v1/payment/order',
  CANCEL: 'https://kapi.kakao.com/v1/payment/cancel'
};

const KAKAOPAY_ADMIN_KEY = process.env.KAKAOPAY_ADMIN_KEY;
const KAKAOPAY_CID = process.env.KAKAOPAY_CID; // Merchant ID (10-digit)

if (!KAKAOPAY_ADMIN_KEY || !KAKAOPAY_CID) {
  console.error('KakaoPay environment variables are missing');
}

/**
 * Prepare a KakaoPay payment transaction
 * @param {Object} params - Payment parameters
 * @param {number} params.amount - Amount in KRW
 * @param {string} params.orderId - Order ID
 * @param {string} params.itemName - Item name (e.g., "KOSMO Foundation Donation")
 * @param {string} params.yourName - Donor name
 * @param {string} params.approvalUrl - Approval URL (redirect after payment)
 * @param {string} params.cancelUrl - Cancel URL
 * @param {string} params.failUrl - Fail URL
 * @returns {Promise<Object>} KakaoPay transaction response
 */
export async function kakaoPayReady(params) {
  try {
    const response = await axios({
      method: 'post',
      url: KAKAOPAY_API_URL.READY,
      headers: {
        'Authorization': `KakaoAK ${KAKAOPAY_ADMIN_KEY}`,
        'Content-Type': 'application/x-www-form-urlencoded;charset=utf-8',
      },
      data: new URLSearchParams({
        cid: KAKAOPAY_CID,
        partner_order_id: params.orderId,
        partner_user_id: params.yourName || 'anonymous',
        item_name: params.itemName,
        quantity: '1',
        total_amount: String(params.amount),
        tax_free_amount: '0',
        approval_url: params.approvalUrl,
        cancel_url: params.cancelUrl,
        fail_url: params.failUrl,
      }).toString()
    });

    return response.data;
  } catch (error) {
    console.error('KakaoPay ready error:', error.response?.data || error.message);
    throw error;
  }
}

/**
 * Approve a KakaoPay payment transaction
 * @param {Object} params - Approval parameters
 * @param {string} params.pgToken - Payment token (from approval redirect)
 * @param {string} params.orderId - Order ID
 * @param {string} params.yourName - Donor name
 * @param {string} params.tid - Transaction ID (from ready response)
 * @returns {Promise<Object>} KakaoPay approval response
 */
export async function kakaoPayApprove(params) {
  try {
    const response = await axios({
      method: 'post',
      url: KAKAOPAY_API_URL.APPROVE,
      headers: {
        'Authorization': `KakaoAK ${KAKAOPAY_ADMIN_KEY}`,
        'Content-Type': 'application/x-www-form-urlencoded;charset=utf-8',
      },
      data: new URLSearchParams({
        cid: KAKAOPAY_CID,
        tid: params.tid,
        partner_order_id: params.orderId,
        partner_user_id: params.yourName || 'anonymous',
        pg_token: params.pgToken,
      }).toString()
    });

    return response.data;
  } catch (error) {
    console.error('KakaoPay approve error:', error.response?.data || error.message);
    throw error;
  }
}

/**
 * Get KakaoPay payment status
 * @param {string} tid - Transaction ID
 * @returns {Promise<Object>} KakaoPay order status
 */
export async function kakaoPayStatus(tid) {
  try {
    const response = await axios({
      method: 'post',
      url: KAKAOPAY_API_URL.ORDER,
      headers: {
        'Authorization': `KakaoAK ${KAKAOPAY_ADMIN_KEY}`,
        'Content-Type': 'application/x-www-form-urlencoded;charset=utf-8',
      },
      data: new URLSearchParams({
        cid: KAKAOPAY_CID,
        tid: tid,
      }).toString()
    });

    return response.data;
  } catch (error) {
    console.error('KakaoPay status error:', error.response?.data || error.message);
    throw error;
  }
}

/**
 * Cancel a KakaoPay payment transaction
 * @param {Object} params - Cancel parameters
 * @param {string} params.tid - Transaction ID
 * @param {number} params.amount - Cancel amount
 * @param {number} params.taxFreeAmount - Tax-free amount in the cancel amount
 * @param {string} params.reason - Cancel reason
 * @returns {Promise<Object>} KakaoPay cancel response
 */
export async function kakaoPayCancel(params) {
  try {
    const response = await axios({
      method: 'post',
      url: KAKAOPAY_API_URL.CANCEL,
      headers: {
        'Authorization': `KakaoAK ${KAKAOPAY_ADMIN_KEY}`,
        'Content-Type': 'application/x-www-form-urlencoded;charset=utf-8',
      },
      data: new URLSearchParams({
        cid: KAKAOPAY_CID,
        tid: params.tid,
        cancel_amount: String(params.amount),
        cancel_tax_free_amount: String(params.taxFreeAmount || '0'),
        cancel_vat_amount: '0',
        cancel_available_amount: String(params.amount),
        payload: params.reason || 'Cancellation requested',
      }).toString()
    });

    return response.data;
  } catch (error) {
    console.error('KakaoPay cancel error:', error.response?.data || error.message);
    throw error;
  }
}

/**
 * Generate a unique order ID
 * @returns {string} Order ID
 */
export function generateOrderId() {
  return `KOSMO-${Date.now()}-${Math.floor(Math.random() * 1000)}`;
}
