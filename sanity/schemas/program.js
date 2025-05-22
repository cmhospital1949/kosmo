export default {
  name: 'program',
  title: 'Program',
  type: 'document',
  fields: [
    {
      name: 'title',
      title: 'English Title',
      type: 'string',
      validation: Rule => Rule.required()
    },
    {
      name: 'koreanTitle',
      title: 'Korean Title',
      type: 'string',
      validation: Rule => Rule.required()
    },
    {
      name: 'slug',
      title: 'Slug',
      type: 'slug',
      options: {
        source: 'title',
        maxLength: 96,
      },
      validation: Rule => Rule.required()
    },
    {
      name: 'heroImage',
      title: 'Hero Image',
      type: 'image',
      options: {
        hotspot: true,
      },
    },
    {
      name: 'excerpt',
      title: 'English Excerpt',
      type: 'text',
      rows: 3,
    },
    {
      name: 'koreanExcerpt',
      title: 'Korean Excerpt',
      type: 'text',
      rows: 3,
    },
    {
      name: 'body',
      title: 'English Body',
      type: 'array',
      of: [
        {
          type: 'block'
        },
        {
          type: 'image',
          fields: [
            {
              type: 'text',
              name: 'alt',
              title: 'Alternative text',
              description: 'Important for SEO and accessibility',
              options: {
                isHighlighted: true
              }
            }
          ]
        }
      ]
    },
    {
      name: 'koreanBody',
      title: 'Korean Body',
      type: 'array',
      of: [
        {
          type: 'block'
        },
        {
          type: 'image',
          fields: [
            {
              type: 'text',
              name: 'alt',
              title: 'Alternative text',
              description: 'Important for SEO and accessibility',
              options: {
                isHighlighted: true
              }
            }
          ]
        }
      ]
    },
    {
      name: 'order',
      title: 'Display Order',
      type: 'number',
      description: 'Lower numbers show first',
      initialValue: 10,
    }
  ],
  preview: {
    select: {
      title: 'title',
      subtitle: 'koreanTitle',
      media: 'heroImage'
    }
  }
}
