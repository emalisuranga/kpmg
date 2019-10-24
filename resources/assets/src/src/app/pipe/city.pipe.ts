import { GeneralService } from './../http/services/general.service';
import { Pipe, PipeTransform } from '@angular/core';
import { ICity, IDistrict } from '../http/models/general.model';

@Pipe({
  name: 'city'
})
export class CityPipe implements PipeTransform {

  transform(value: Array<ICity>, args?: IDistrict): any {
    if (args === undefined || args === null || args.id  === undefined) { return ; }
    return value.filter(Obj => Obj.district_code === args.id.toString());
  }

}
