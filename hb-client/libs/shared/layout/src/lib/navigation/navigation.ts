import * as fromSharedFuse from '@hourly-board-workspace/shared/fuse';

export const navigation: fromSharedFuse.FuseNavigation[] = [
  {
    id: 'Planning',
    title: 'Planning',
    translate: 'NAV.Planning',
    type: 'collapsable',
    icon: 'assessment',
    children: [
      {
        id: 'planning-overview',
        title: 'Planning Overview',
        translate: 'NAV.PlanningOverview',
        type: 'item',
        icon:'aspect_ratio',
        url: '/management/planning-overview'
      }
      // {
      //     id   : 'search-modern',
      //     title: 'Modern',
      //     type : 'item',
      //     url  : '/pages/search/modern'
      // }
    ]
  }
];
