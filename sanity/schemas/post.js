export default {
  name: 'post',
  title: 'News Post',
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
      name: 'coverImage',
      title: 'Cover Image',
      type: 'image',
      options: {
        hotspot: true,
      },
    },
    {
      name: 'category',
      title: 'Category',
      type: 'string',
      options: {
        list: [
          { title: 'Programs', value: 'Programs' },
          { title: 'Partnerships', value: 'Partnerships' },
          { title: 'Events', value: 'Events' },
          { title: 'Research', value: 'Research' },
          { title: 'Announcements', value: 'Announcements' },
        ],
      },
    },
    {
      name: 'publishedAt',
      title: 'Published at',
      type: 'datetime',
      initialValue: (new Date()).toISOString(),
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
    }
  ],
  preview: {
    select: {
      title: 'title',
      subtitle: 'publishedAt',
      media: 'coverImage'
    },
    prepare(selection) {
      const { title, subtitle, media } = selection;
      const date = subtitle ? new Date(subtitle).toLocaleDateString() : '';
      return {
        title,
        subtitle: date,
        media
      }
    }
  }
}
