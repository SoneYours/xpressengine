@section('page_head')
<h2><a href="{{route('settings.menu.select.types', $menu->id)}}"><i class="xi-arrow-left"></i></a>{{xe_trans('xe::newItem')}}</h2>
<div class="tit_btn_area">
    <button type="button" class="btn_lst_toggle visible-xs pull-right"><i class="xi-ellipsis-v"></i></button>
    <ul class="btn_lst"></ul>
</div>
<div class="tit_bottom">
    <ul class="locate">
        <li>
            <a href="{{ route('settings.menu.edit.menu', $menu->id) }}">
                {{$menu->title}}
            </a>
            <i class="xi-angle-right"></i>
        </li>
    </ul>
</div>
@endsection
@extends('menu.layout')
@section('menuContent')
<form action="{{ route('settings.menu.store.item', $menu->id) }}" method="post">
    <input type="hidden" name="_token" value="{{ Session::token() }}"/>
    <input type="hidden" name="selectedType" value="{{ $selectedType }}"/>
    <input type="hidden" name="siteKey" value="{{ $siteKey }}"/>
    <input type="hidden" name="menuId" value="{{ $menu->id}}"/>
    <div class="col-md-12">
        <div class="panel-group">
            <div class="panel">
                <div class="panel-heading">
                    <div class="pull-left">
                        <h3 class="panel-title">{{xe_trans('xe::newItemDescription')}}</h3>
                    </div>
                    <div class="pull-right">
                        <a data-toggle="collapse" data-parent="#accordion" href="#collapseOne" class="btn-link panel-toggle pull-right"><i class="xi-angle-down"></i><i class="xi-angle-up"></i><span class="sr-only">메뉴닫기</span></a>
                    </div>
                </div>

                <div class="panel-body" id="collapseOne">
                    <div class="form-group">
                        <label for="item-active">Item Activated<br><small>{{xe_trans('xe::itemActivatedDescription')}}</small></label>

                        <div class="xe-btn-toggle pull-right">
                            <label>
                                <span class="sr-only"><span class="sr-only">활성화 비활성화</span></span>
                                <input type="checkbox" checked="" id="item-active" name="itemActivated">
                                <span class="toggle"></span>
                            </label>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="item-url">
                            Item Url
                        </label>

                        @if($menuType::isRouteAble())
                        <em class="txt_blue">/</em>
                        @endif
                        <input type="text" id="item-url" name="itemUrl" class="xe-input-text" value="{{Request::old('itemUrl')}}"/>
                    </div>
                    <div class="form-group">
                        <label for="item-title">Item Title</label>
                        <input type="hidden" name="itemOrdering" value="0" readonly/>
                        <div class="row">
                            <div class="col-sm-4">
                                <select name="parent" class="form-control">
                                    <option value="{{$menu->id}}" @if($parent == $menu->id) selected @endif>
                                        {{$menu->title}}
                                    </option>
                                    @include('menu.partial.itemOption', ['items' => $menu->items, 'maxDepth' => $maxDepth])
                                </select>
                            </div>
                            <div class="col-sm-8">
                                <div class="inpt_bd">
                                    {!! uio('langText', ['id' => 'itemTitle', 'langKey'=> '', 'name'=>'itemTitle']) !!}
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="item-description">Item Description</label>
                        <textarea name="itemDescription" id="item-description" class="form-control" rows="3"placeholder="{{xe_trans('xe::itemDescriptionPlaceHolder')}}"></textarea>
                    </div>
                    <div class="form-group">
                        <label for="item-target">Item Target<br><small>{{xe_trans('xe::itemTargetDescription')}}</small></label>

                        <select name="itemTarget" class="form-control">
                            <option value="_self" selected>
                                {{xe_trans('xe::itemTargetOption_sameFrame')}}
                            </option>
                            <option value="_black">
                                {{xe_trans('xe::itemTargetOption_newWindow')}}
                            </option>
                            <option value="_parent">
                                {{xe_trans('xe::itemTargetOption_parentFrame')}}
                            </option>
                            <option value="_top">
                                {{xe_trans('xe::itemTargetOption_topFrame')}}
                            </option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="panel">
                <div class="panel-heading">
                    <div class="pull-left">
                        <h3 class="panel-title">Theme<small>{{xe_trans('xe::menuThemeDescription')}}</small></h3>
                    </div>
                    <div class="pull-right">
                        <a data-toggle="collapse" data-parent="#accordion" href="#collapseTwo" class="btn-link panel-toggle pull-right"><i class="xi-angle-down"></i><i class="xi-angle-up"></i><span class="sr-only">메뉴닫기</span></a>
                    </div>
                </div>
                <div id="collapseTwo" class="panel-collapse collapse in">
                    <div class="panel-heading">
                        <div class="pull-left">
                            <div class="checkbox">
                                <label>
                                    <input type="checkbox" name="parentTheme" id="parentTheme" value="1">
                                    {{xe_trans('xe::menuThemeInheritMode')}}
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="panel-body">
                        <div class="form-group">
                            <div id="themeSelect">
                                {!! uio('themeSelect', ['selectedTheme' => ['desktop' => $menuConfig->get('desktopTheme'), 'mobile' => $menuConfig->get('mobileTheme')]]) !!}
                            </div>
                        </div>
                    </div>
                </div>

                <div class="panel-footer">
                </div>
            </div>
        </div>

        {!! uio('menuType', $menuTypeArgs) !!}

        <div class="pull-right">
            <a href="{{ route('settings.menu.select.types')}}" class="btn btn-default">{{xe_trans('xe::cancel')}}</a>
            <button type="submit" class="btn btn-primary">{{xe_trans('xe::submit')}}</button>
        </div>
    </div>
</form>
<script>
    $('#parentTheme').change(function (e) {
        $(e.target).closest('.panel-collapse').find('>.panel-body').toggle();
    });
</script>
@endsection
