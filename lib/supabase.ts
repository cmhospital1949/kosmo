import { createClient } from '@supabase/supabase-js';

// Create a single supabase client for the entire app
const supabaseUrl = process.env.NEXT_PUBLIC_SUPABASE_URL;
const supabaseKey = process.env.SUPABASE_SERVICE_ROLE_KEY;

if (!supabaseUrl || !supabaseKey) {
  throw new Error('Missing environment variables for Supabase');
}

export const supabase = createClient(supabaseUrl, supabaseKey);

// Donations
export async function createDonation(donationData) {
  const { data, error } = await supabase
    .from('donations')
    .insert([donationData])
    .select();
  
  if (error) throw error;
  return data?.[0];
}

export async function getDonationByTid(tid) {
  const { data, error } = await supabase
    .from('donations')
    .select('*')
    .eq('tid', tid)
    .single();
  
  if (error) throw error;
  return data;
}

export async function updateDonationStatus(id, status, transactionDetails = {}) {
  const { data, error } = await supabase
    .from('donations')
    .update({ 
      status, 
      updated_at: new Date().toISOString(),
      ...transactionDetails
    })
    .eq('id', id)
    .select();
  
  if (error) throw error;
  return data?.[0];
}

// Contact Form Submissions
export async function saveContactSubmission(formData) {
  const { data, error } = await supabase
    .from('contact_submissions')
    .insert([{
      name: formData.name,
      email: formData.email,
      message: formData.message,
      created_at: new Date().toISOString()
    }])
    .select();
  
  if (error) throw error;
  return data?.[0];
}

// Newsletter Subscribers
export async function addNewsletterSubscriber(email) {
  // Check if email already exists
  const { data: existingSubscriber } = await supabase
    .from('subscribers')
    .select('*')
    .eq('email', email)
    .single();
  
  if (existingSubscriber) {
    return { id: existingSubscriber.id, alreadySubscribed: true };
  }
  
  // Add new subscriber
  const { data, error } = await supabase
    .from('subscribers')
    .insert([{
      email,
      created_at: new Date().toISOString()
    }])
    .select();
  
  if (error) throw error;
  return { id: data?.[0].id, alreadySubscribed: false };
}

// Volunteer Applications
export async function saveVolunteerApplication(applicationData) {
  const { data, error } = await supabase
    .from('volunteer_applications')
    .insert([{
      ...applicationData,
      created_at: new Date().toISOString()
    }])
    .select();
  
  if (error) throw error;
  return data?.[0];
}
