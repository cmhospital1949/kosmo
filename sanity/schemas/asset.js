export default {
  name: 'asset',
  title: 'Asset',
  type: 'document',
  fields: [
    {
      name: 'url',
      title: 'URL',
      type: 'url',
      validation: Rule => Rule.required()
    },
    {
      name: 'alt',
      title: 'Alt Text',
      type: 'string'
    },
    {
      name: 'width',
      title: 'Width',
      type: 'number'
    },
    {
      name: 'height',
      title: 'Height',
      type: 'number'
    },
    {
      name: 'licenseType',
      title: 'License Type',
      type: 'string',
      options: {
        list: [
          {title: 'Public Domain', value: 'public_domain'},
          {title: 'Creative Commons', value: 'cc'},
          {title: 'Commercial', value: 'commercial'},
          {title: 'Custom', value: 'custom'}
        ]
      }
    },
    {
      name: 'credit',
      title: 'Credit/Attribution',
      type: 'string'
    },
    {
      name: 'licenseDetails',
      title: 'License Details',
      type: 'text'
    }
  ],
  preview: {
    select: {
      title: 'alt',
      subtitle: 'licenseType',
      media: 'url'
    }
  }
}
