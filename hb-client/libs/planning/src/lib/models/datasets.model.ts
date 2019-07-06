export interface DataSetsModel {
  timeStamp?: string;
  timeOut?: number;
  startingWith?: string;
  rows: DataSetsLineModel[];
}

export interface DataSetsLineModel {
  lineId?: number;
  lineName?: string;
  date?: string;
  shift?: string;
  planningType?: string;
  planningTypes?:any[];
  activeOnBillboard?: string;
  targetsPerHour?: string[];
  totals?: number;
  dailyTargetId?: number;
  location?: string;
  tags?: string[];
  open?: boolean;
}

// const data = [
//   {
//     key: 'AUDI',
//     value: [
//       {
//         key: '2019-2-19',
//         value: [
//           {
//             key: 'DY',
//             value: [{ line: '', name: '', targetPerHour: '' }]
//           },
//           {
//             key: 'PN',
//             value: [{ line: '', name: '', targetPerHour: '' }]
//           }
//         ]
//       },
//       {
//         key: '2019-2-18',
//         value: [
//           {
//             key: 'DY',
//             value: [{ line: '', name: '', targetPerHour: '' }]
//           },
//           {
//             key: 'PN',
//             value: [{ line: '', name: '', targetPerHour: '' }]
//           }
//         ]
//       }
//     ]
//   }
// ];
