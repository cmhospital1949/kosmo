import { createClient } from '@sanity/client';
import imageUrlBuilder from '@sanity/image-url';

export const client = createClient({
  projectId: process.env.NEXT_PUBLIC_SANITY_PROJECT_ID,
  dataset: process.env.NEXT_PUBLIC_SANITY_DATASET || 'production',
  apiVersion: '2023-11-18', // use current date (YYYY-MM-DD) to target the latest API version
  useCdn: process.env.NODE_ENV === 'production', // `false` if you want to ensure fresh data
  token: process.env.SANITY_API_TOKEN, // Only needed for studio access
});

// Helper function for Sanity images
export const urlFor = (source) => {
  return imageUrlBuilder(client).image(source);
};

// Fetch all programs
export async function getAllPrograms(locale = 'en') {
  const programData = await client.fetch(`
    *[_type == "program"] | order(order asc) {
      _id,
      title,
      koreanTitle,
      slug,
      "heroImage": heroImage.asset->url,
      excerpt,
      koreanExcerpt,
      body,
      koreanBody,
      order
    }
  `);
  
  // Transform the data based on locale
  return programData.map(program => ({
    id: program._id,
    title: locale === 'en' ? program.title : program.koreanTitle,
    koreanTitle: program.koreanTitle,
    slug: program.slug.current,
    heroImage: program.heroImage,
    excerpt: locale === 'en' ? program.excerpt : program.koreanExcerpt,
    content: locale === 'en' ? program.body : program.koreanBody,
  }));
}

// Fetch a single program by slug
export async function getProgramBySlug(slug, locale = 'en') {
  const programData = await client.fetch(`
    *[_type == "program" && slug.current == $slug][0] {
      _id,
      title,
      koreanTitle,
      slug,
      "heroImage": heroImage.asset->url,
      excerpt,
      koreanExcerpt,
      body,
      koreanBody
    }
  `, { slug });
  
  if (!programData) return null;
  
  // Transform the data based on locale
  return {
    id: programData._id,
    title: locale === 'en' ? programData.title : programData.koreanTitle,
    koreanTitle: programData.koreanTitle,
    slug: programData.slug.current,
    heroImage: programData.heroImage,
    excerpt: locale === 'en' ? programData.excerpt : programData.koreanExcerpt,
    content: locale === 'en' ? programData.body : programData.koreanBody,
  };
}

// Fetch all news posts
export async function getAllPosts(locale = 'en', limit = 100) {
  const postData = await client.fetch(`
    *[_type == "post"] | order(publishedAt desc) [0...$limit] {
      _id,
      title,
      koreanTitle,
      slug,
      "coverImage": coverImage.asset->url,
      category,
      publishedAt,
      excerpt,
      koreanExcerpt,
      body,
      koreanBody
    }
  `, { limit });
  
  // Transform the data based on locale
  return postData.map(post => ({
    id: post._id,
    title: locale === 'en' ? post.title : post.koreanTitle,
    slug: post.slug.current,
    image: post.coverImage,
    category: post.category,
    date: post.publishedAt,
    excerpt: locale === 'en' ? post.excerpt : post.koreanExcerpt,
    content: locale === 'en' ? post.body : post.koreanBody,
  }));
}

// Fetch a single post by slug
export async function getPostBySlug(slug, locale = 'en') {
  const postData = await client.fetch(`
    *[_type == "post" && slug.current == $slug][0] {
      _id,
      title,
      koreanTitle,
      slug,
      "coverImage": coverImage.asset->url,
      category,
      publishedAt,
      excerpt,
      koreanExcerpt,
      body,
      koreanBody
    }
  `, { slug });
  
  if (!postData) return null;
  
  // Transform the data based on locale
  return {
    id: postData._id,
    title: locale === 'en' ? postData.title : postData.koreanTitle,
    slug: postData.slug.current,
    image: postData.coverImage,
    category: postData.category,
    date: postData.publishedAt,
    excerpt: locale === 'en' ? postData.excerpt : postData.koreanExcerpt,
    content: locale === 'en' ? postData.body : postData.koreanBody,
  };
}

// Fetch related posts by category
export async function getRelatedPosts(category, currentPostId, locale = 'en', limit = 3) {
  const postData = await client.fetch(`
    *[_type == "post" && category == $category && _id != $currentPostId] | order(publishedAt desc) [0...$limit] {
      _id,
      title,
      koreanTitle,
      slug,
      "coverImage": coverImage.asset->url,
      category,
      publishedAt,
      excerpt,
      koreanExcerpt
    }
  `, { category, currentPostId, limit });
  
  // Transform the data based on locale
  return postData.map(post => ({
    id: post._id,
    title: locale === 'en' ? post.title : post.koreanTitle,
    slug: post.slug.current,
    image: post.coverImage,
    category: post.category,
    date: post.publishedAt,
    excerpt: locale === 'en' ? post.excerpt : post.koreanExcerpt,
  }));
}

// Create a new donation record
export async function createDonation(donationData) {
  return client.create({
    _type: 'donation',
    ...donationData
  });
}

// Update a donation record
export async function updateDonation(id, donationData) {
  return client.patch(id).set(donationData).commit();
}
