export default {
  name: 'localizedString',
  title: 'Localized String',
  type: 'document',
  fields: [
    {
      name: 'key',
      title: 'Key',
      type: 'string',
      validation: Rule => Rule.required()
    },
    {
      name: 'locale',
      title: 'Locale',
      type: 'string',
      options: {
        list: [
          {title: 'English', value: 'en'},
          {title: 'Korean', value: 'ko'}
        ]
      },
      validation: Rule => Rule.required()
    },
    {
      name: 'value',
      title: 'Value',
      type: 'string',
      validation: Rule => Rule.required()
    }
  ],
  preview: {
    select: {
      title: 'key',
      subtitle: 'locale'
    },
    prepare(selection) {
      const { title, subtitle } = selection;
      return {
        title,
        subtitle: subtitle === 'en' ? '🇺🇸 English' : '🇰🇷 Korean'
      }
    }
  }
}
