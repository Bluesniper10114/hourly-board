export interface SettingsModel {
  ratingScales: [
    {
      id?:number;
      name?:string;
      isActive?:boolean;
      selectableByUse?:boolean;
    }
  ],
  rankingCategories: [
    {
      id?:number;
      name?:string;
      isActive?:boolean;
      rating?:number;
      ratingScale: {
        id?:number;
        name?:string;
        isActive?:boolean;
        selectableByUse?:boolean;
      }
    }
  ],
  userID?:number;
  userInfo: {
    firstName?:string;
    lastName?:string;
    username?:string;
    image?:string;
    emailId?:string;
    emailSignature?:string;
    phoneNumber?:string;
    sendEmailFrom?:string;
  },
  features: {
    isSalesFeatureEnabled?:boolean;
  }
}
