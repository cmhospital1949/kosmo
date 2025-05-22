import { NextResponse } from 'next/server';
import { kakaoPayReady, generateOrderId } from '@/lib/kakao';
import { createDonation } from '@/lib/supabase';

export async function POST(request) {
  try {
    // Parse request body
    const body = await request.json();
    const { amount, donorName, donorEmail, isAnonymous } = body;
    
    // Validate amount
    if (!amount || isNaN(amount) || amount < 1000) {
      return NextResponse.json(
        { error: 'Invalid donation amount. Minimum is 1,000 KRW.' },
        { status: 400 }
      );
    }
    
    // Generate order ID
    const orderId = generateOrderId();
    
    // Prepare KakaoPay transaction
    const baseUrl = process.env.NEXT_PUBLIC_SITE_URL || 'http://localhost:3000';
    const kakaoResponse = await kakaoPayReady({
      amount,
      orderId,
      itemName: 'KOSMO Foundation Donation',
      yourName: isAnonymous ? 'anonymous' : (donorName || 'undefined'),
      approvalUrl: `${baseUrl}/donate/confirm?orderId=${orderId}`,
      cancelUrl: `${baseUrl}/donate/cancel`,
      failUrl: `${baseUrl}/donate/failed`,
    });
    
    // Create donation record in the database
    const donation = await createDonation({
      amount,
      payment_gateway: 'kakaopay',
      tid: kakaoResponse.tid,
      status: 'pending',
      donor_name: isAnonymous ? null : donorName,
      donor_email: isAnonymous ? null : donorEmail,
      is_anonymous: isAnonymous,
      order_id: orderId,
      created_at: new Date().toISOString(),
    });
    
    // Return KakaoPay redirect URL and transaction info
    return NextResponse.json({
      redirectUrl: kakaoResponse.next_redirect_pc_url,
      tid: kakaoResponse.tid,
      orderId,
      donationId: donation.id,
    });
  } catch (error) {
    console.error('KakaoPay ready endpoint error:', error);
    
    return NextResponse.json(
      { error: 'Failed to prepare payment. Please try again.' },
      { status: 500 }
    );
  }
}
