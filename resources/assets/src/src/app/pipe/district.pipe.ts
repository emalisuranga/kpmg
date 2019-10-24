import { Pipe, PipeTransform } from '@angular/core';
import { IProvince, IDistrict } from '../http/models/general.model';

@Pipe({
  name: 'district'
})
export class DistrictPipe implements PipeTransform {

  transform(value: Array<IDistrict>, args?: IProvince): any {
    if (args === undefined || args === null || args.id  === undefined) { return; }
    return value.filter(Obj => Obj.province_code === args.id.toString());
  }

}
