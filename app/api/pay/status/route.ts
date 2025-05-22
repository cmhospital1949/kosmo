import { NextResponse } from 'next/server';
import { kakaoPayStatus } from '@/lib/kakao';
import { getDonationByTid } from '@/lib/supabase';

export async function GET(request) {
  // Extract tid from URL
  const { searchParams } = new URL(request.url);
  const tid = searchParams.get('tid');
  
  if (!tid) {
    return NextResponse.json(
      { error: 'Missing transaction ID (tid).' },
      { status: 400 }
    );
  }
  
  try {
    // Get donation record from database
    const donation = await getDonationByTid(tid);
    
    if (!donation) {
      return NextResponse.json(
        { error: 'Donation record not found.' },
        { status: 404 }
      );
    }
    
    // If the donation is already marked as 'paid', return success
    if (donation.status === 'paid') {
      return NextResponse.json({
        status: 'paid',
        donationId: donation.id,
        amount: donation.amount,
        createdAt: donation.created_at,
        approvedAt: donation.approved_at,
      });
    }
    
    // Check payment status in KakaoPay
    const kakaoStatus = await kakaoPayStatus(tid);
    
    // Return payment status information
    return NextResponse.json({
      status: donation.status,
      paymentStatus: kakaoStatus.status,
      donationId: donation.id,
      amount: donation.amount,
      createdAt: donation.created_at,
    });
  } catch (error) {
    console.error('Payment status check error:', error);
    
    return NextResponse.json(
      { error: 'Failed to check payment status. Please try again.' },
      { status: 500 }
    );
  }
}
