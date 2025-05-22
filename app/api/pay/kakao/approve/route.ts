import { NextResponse } from 'next/server';
import { kakaoPayApprove } from '@/lib/kakao';
import { getDonationByTid, updateDonationStatus } from '@/lib/supabase';

export async function POST(request) {
  try {
    // Parse request body
    const body = await request.json();
    const { pgToken, orderId, tid } = body;
    
    // Validate required fields
    if (!pgToken || !orderId || !tid) {
      return NextResponse.json(
        { error: 'Missing required fields for payment approval.' },
        { status: 400 }
      );
    }
    
    // Get donation record
    const donation = await getDonationByTid(tid);
    
    if (!donation) {
      return NextResponse.json(
        { error: 'Donation record not found.' },
        { status: 404 }
      );
    }
    
    // Approve KakaoPay transaction
    const kakaoResponse = await kakaoPayApprove({
      pgToken,
      orderId,
      tid,
      yourName: donation.donor_name || 'anonymous',
    });
    
    // Update donation status
    const updatedDonation = await updateDonationStatus(
      donation.id, 
      'paid',
      {
        approved_at: new Date().toISOString(),
        payment_details: JSON.stringify(kakaoResponse),
      }
    );
    
    // Return success response
    return NextResponse.json({
      success: true,
      donationId: donation.id,
      amount: donation.amount,
      status: 'paid',
      approvedAt: updatedDonation.approved_at,
    });
  } catch (error) {
    console.error('KakaoPay approve endpoint error:', error);
    
    return NextResponse.json(
      { error: 'Failed to approve payment. Please contact support.' },
      { status: 500 }
    );
  }
}
