import { NextResponse } from 'next/server';
import { saveContactSubmission } from '@/lib/supabase';

export async function POST(request) {
  try {
    // Parse request body
    const body = await request.json();
    const { name, email, message } = body;
    
    // Validate required fields
    if (!name || !email || !message) {
      return NextResponse.json(
        { error: 'All fields are required.' },
        { status: 400 }
      );
    }
    
    // Validate email format
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(email)) {
      return NextResponse.json(
        { error: 'Invalid email format.' },
        { status: 400 }
      );
    }
    
    // Save submission to database
    const submission = await saveContactSubmission({ name, email, message });
    
    // In a real implementation, we would also send an email here
    // This could be done using a service like SendGrid, AWS SES, etc.
    
    // Return success response
    return NextResponse.json({
      success: true,
      submissionId: submission.id,
      message: 'Your message has been received. We will get back to you soon!',
    });
  } catch (error) {
    console.error('Contact form submission error:', error);
    
    return NextResponse.json(
      { error: 'Failed to process your request. Please try again later.' },
      { status: 500 }
    );
  }
}
