var options = {
	openingHTML: (
		<div className="resultsBox col-sm-10">
			<div id="initialMessage" className="extrapanel panel panel-primary">
				<div className="panel-body">
					<p>Welcome to Pok&eacute;mon database! Here you can filter Pok&eacute;mon by many different properties.</p>
					<p>Pick some of the filters on the left or type something in the search box to begin your search!</p>
				</div>
			</div>
		</div>
	), 
	noResultsHTML: (
		<div className="resultsBox col-sm-10">
			<div id="initialMessage" className="extrapanel panel panel-primary">
				<div className="panel-body">
					<p>No results found.</p>
				</div>
			</div>
		</div>
	), 
	searchBreadcrumbClass: "btn-primary",
	sidebars: [
		{
			name: "Types", 
			breadcrumbName: "Type", 
			breadcrumbClass: "btn-danger", 
			filterType: "t",
			getUrl: "example-pokemon.php?function=GetTypes"
		},
		{
			name: "Egg Groups", 
			breadcrumbName: "Egg Group", 
			breadcrumbClass: "btn-success", 
			filterType: "e",
			getUrl: "example-pokemon.php?function=GetEggGroups"
		},
		{
			name: "Generations", 
			breadcrumbName: "Generation", 
			breadcrumbClass: "btn-warning", 
			filterType: "g",
			getUrl: "example-pokemon.php?function=GetGenerations"
		},
		{
			name: "Stages", 
			breadcrumbName: "Stage", 
			breadcrumbClass: "btn-info", 
			filterType: "s",
			getUrl: "example-pokemon.php?function=GetStages"
		},
		{
			name: "Body Types", 
			breadcrumbName: "Body Type", 
			breadcrumbClass: "btn-primary", 
			filterType: "b",
			getUrl: "example-pokemon.php?function=GetBodyTypes"
		},
		{
			name: "Base Experience", 
			breadcrumbName: "Base Experience", 
			breadcrumbClass: "btn-primary", 
			filterType: "be",
			getUrl: "example-pokemon.php?function=GetBaseExperiences"
		},
		{ name: "EVs: HP", breadcrumbName: "HP EV", breadcrumbClass: "btn-danger", filterType: "ehp", getUrl: "example-pokemon.php?function=GetHpEVs" }, 
		{ name: "EVs: Attack", breadcrumbName: "Attack EV", breadcrumbClass: "btn-danger", filterType: "eatk", getUrl: "example-pokemon.php?function=GetAtkEVs" }, 
		{ name: "EVs: Defense", breadcrumbName: "Defense EV", breadcrumbClass: "btn-danger", filterType: "edef", getUrl: "example-pokemon.php?function=GetDefEVs" }, 
		{ name: "EVs: Special Attack", breadcrumbName: "Sp. Attack EV", breadcrumbClass: "btn-danger", filterType: "esatk", getUrl: "example-pokemon.php?function=GetSpAtkEVs" }, 
		{ name: "EVs: Special Defense", breadcrumbName: "Sp. Defense EV", breadcrumbClass: "btn-danger", filterType: "esdef", getUrl: "example-pokemon.php?function=GetSpDefEVs" }, 
		{ name: "EVs: Speed", breadcrumbName: "Speed EV", breadcrumbClass: "btn-danger", filterType: "espd", getUrl: "example-pokemon.php?function=GetSpdEVs" }
	],
	contentURL: "example-pokemon.php?function=PokeSearch",
	contentSingleURL: "example-pokemon.php?function=GetSinglePokemon",
	collapseSidebars: "all"
};

var disp = decodeURIComponent;
function Optional(val, html) { return (val == null || val == "") ? "" : html; }
function ApplyStyles(str) {
	if(str == undefined) { return str; }
	var o = disp(str).split("**").map(function(v, i) { return i % 2 != 0 ? (<i key={i}>{v}</i>) : v; });
	return (<span>{o}</span>);
}
function CollapsePane(collapseBtn) {
	var collapser = collapseBtn.closest(".collapseArea").find(".interior");
	var colSpan = collapseBtn.find("span");
	if(colSpan.hasClass("glyphicon-menu-up")) {
		colSpan.removeClass("glyphicon-menu-up").addClass("glyphicon-menu-down");
		collapser.collapse("hide");
	} else {
		colSpan.removeClass("glyphicon-menu-down").addClass("glyphicon-menu-up");
		collapser.collapse("show");
	}
}

var FullContainer = React.createClass({
	getInitialState: function() { return { wordData: null, breadcrumbs: [], sources: [] }; },
	componentDidMount: function() {
		if(this.props.options.sourcesURL == undefined || this.props.options.sourcesURL == "") { return; }
		$.ajax({
			url: this.props.options.sourcesURL,
			dataType: "JSON",
			success: function(data) {
				if(!data.success) { return; }
				this.setState({sources: data.result});
			}.bind(this)
		});
	},
	addFilterItem: function (breadcrumbData, replace) {
		var breadcrumbs = this.state.breadcrumbs;
		if(replace) {
			var indexesToRemove = [];
			for(var i = 0; i < breadcrumbs.length; i++) {
				if(breadcrumbs[i].filterType == breadcrumbData.filterType) { indexesToRemove.push(i); }
			}
			for(var i = indexesToRemove.length - 1; i >= 0; i--) {
				breadcrumbs.splice(indexesToRemove[i], 1);
			}
		}	
		breadcrumbs.push(breadcrumbData);
		this.refreshWordsFromBreadcrumbs(breadcrumbs);
	}, 
	removeFilterItem: function (breadcrumbName) {
		var breadcrumbs = this.state.breadcrumbs;
		var dataIdx = -1;
		for(var i = 0; i < breadcrumbs.length; i++) {
			if(breadcrumbs[i].displayName == breadcrumbName) {
				dataIdx = i;
				break;
			}
		}
		breadcrumbs.splice(dataIdx, 1);
		this.refreshWordsFromBreadcrumbs(breadcrumbs);
	},
	refreshWordsFromBreadcrumbs: function(newBreadcrumbs) {
		var filterData = [];
		for(var i = 0; i < newBreadcrumbs.length; i++) { filterData.push({ key: newBreadcrumbs[i].filterType, value: newBreadcrumbs[i].filterValue }); }
		if(filterData.length == 0) {
			this.setState({resultData: [], breadcrumbs: newBreadcrumbs});
			return;
		}
		$.ajax({
			type: "POST", 
			url: this.props.options.contentURL,
			dataType: "JSON",
			data: {"filters": JSON.stringify(filterData) }, 
			success: function(data) {
				if(!data.success) { return; }
				this.setState({resultData: data.result, breadcrumbs: newBreadcrumbs});
			}.bind(this)
		});
	},
	handleSearch: function(searchText) {
		this.addFilterItem({
			displayName: "Search: " + disp(searchText), 
			displayClass: this.props.options.searchBreadcrumbClass, 
			filterType: "search", 
			filterValue: searchText
		}, true);
	},
	render: function() {
		return (
			<span>
				<SearchBar className="searchTop" onSearch={this.handleSearch} onRemoveBreadcrumb={this.removeFilterItem} breadcrumbs={this.state.breadcrumbs}/>
				<SideBar options={this.props.options} filters={this.state.breadcrumbs} addSidebarValue={this.addFilterItem} />
				<ResultDisplayBox options={this.props.options} singleURL={this.props.options.contentSingleURL} resultData={this.state.resultData} sources={this.state.sources}/>
			</span>
		);
	}
});

var ResultDisplayBox = React.createClass({
	render: function() {
		if(this.props.resultData == null) { return this.props.options.openingHTML; }
		var sources = this.props.sources, singleURL = this.props.singleURL;
		var	results = this.props.resultData.map(function(r) { return (<SingleValue singleURL={singleURL} key={r.id} data={r} sources={sources}/>); });
		return results.length == 0 ? this.props.options.noResultsHTML : (<div className="resultsBox col-sm-10">{results}</div>);
	}
});
var SingleValue = React.createClass({
	getInitialState: function() { return { source: null, singleData: null }; },
	showSource: function(num) {
		if(this.props.sources == null || this.props.sources.length == 0) { return; }
		this.setState({source: this.props.sources[num-1]});
	},
	getSingleElement: function(elemId) {
		$.ajax({
			type: "GET",
			url: this.props.singleURL + elemId,
			dataType: "JSON",
			success: function(data) {
				if(!data.success) { return; }
				this.setState({"singleData": data.result[0]});
			}.bind(this)
		});
	}, 
	render: function() {
		var wd = this.props.data, showSource = this.showSource;
		var typesArr = wd.types.split(",");
		var types = typesArr.length == 2 ? disp(typesArr.join("/")) : disp(typesArr[0]);
		var singleData = this.state.singleData == null ? "" : (<SingleValue singleURL={this.props.singleURL} key={this.state.singleData.id} data={this.state.singleData} sources={this.props.sources}/>);
		return (
			<div>
				<div className = "valueContainer">
					<div className = "resultVal panel panel-default">
						<div className="panel-heading">
							<span className="word" title={wd.id}>{disp(wd.name)}</span>
							<span className = "langs">{types}</span>
						</div>
						<div className="panel-body">
							<div className="col-md-12">
								<div><strong>Pok&eacute;dex Data:</strong> {disp(wd.desc)}</div>
								{Optional(wd.childID, <div><strong>Evolves from:</strong> <a href="#" onClick={this.getSingleElement.bind(this, wd.childID)}>{disp(wd.childName)}</a></div>)}
								{Optional(wd.parentID, <div><strong>Evolves into:</strong> <a href="#" onClick={this.getSingleElement.bind(this, wd.parentID)}>{disp(wd.parentName)}</a></div>)}
							</div>
						</div>
					</div>
				</div>
				{singleData}
			</div>
		);
	}
});
var SourceButton = React.createClass({
	clickSource: function() { this.props.showSource(this.props.number); },
	render: function() { return (<a href="#" onClick={this.clickSource}><span className="badge sourcebadge">{this.props.number}</span></a>); }
});

var SideBar = React.createClass({
	render: function() {
		var id = 0, filters = this.props.filters, addSidebarValue = this.props.addSidebarValue, sidebar = this.props.options.collapseSidebars;
		var results = this.props.options.sidebars.map(function(s) {
			var key = id++;
			return (<SidebarModule key={key} id={key} sidebar={sidebar} options={s} filters={filters} onSelectSidebarValue={addSidebarValue}/>);
		});
		return (<div className="col-sm-2 blog-sidebar">{results}</div>);
	}
});
var SidebarModule = React.createClass({
	getInitialState: function() { return { values: [] }; },
	componentDidMount: function() { this.triggerStateChange(); },
	triggerStateChange: function() {
		var filterData = [];
		for(var i = 0; i < this.props.filters.length; i++) { filterData.push({ key: this.props.filters[i].filterType, value: this.props.filters[i].filterValue }); }
		$.ajax({
			type: "POST",
			url: this.props.options.getUrl,
			data: {"filters": JSON.stringify(filterData) },
			dataType: "JSON",
			success: function(data) {
				if(!data.success) { return; }
				this.setState({"values": data.result});
			}.bind(this)
		});
	},
	collapse: function() { CollapsePane($(this.refs.collapseBtn)); },
	componentWillReceiveProps: function(p) {
		if(p == null || p.length == 0) { return; }
		this.triggerStateChange();
	},
	filterSidebar: function() {
		var filterValue = this.refs.searchSideBarInput.value.toLowerCase();
		for(var i = 0; i < this.state.values.length; i++) { this.state.values[i].hide = (this.state.values[i].name.toLowerCase().indexOf(filterValue) != 0); }
		this.forceUpdate();
	},
	render: function() {
		var options = this.props.options;
		var onSelectSidebarValue = this.props.onSelectSidebarValue;
		var results = this.state.values.map(function(l) { return (<SideBarListItem key={l.id} data={l} options={options} onSelectSidebarValue={onSelectSidebarValue}/>); });
		var collapseThis = this.props.sidebar == "all" || (this.props.sidebar == "first" && this.props.id > 0);
		var glyphClass = collapseThis ? "glyphicon-menu-down" : "glyphicon-menu-up";
		var additionalCSS = collapseThis ? "" : "in";
		return (
			<div className="sidebar-module sidebar-module-inset collapseArea">
				<h4>{this.props.options.name} <a href="#" className="collapseBtn" ref="collapseBtn" onClick={this.collapse}><span className={"glyphicon " + glyphClass}></span></a></h4>
				<div className={"interior collapse " + additionalCSS} data-toggle="collapse">
					<div className="input-group input-group-sm">
						<input type="text" className="form-control filter-search" ref="searchSideBarInput" onChange={this.filterSidebar} />
						<span className="input-group-btn">
							<button className="btn btn-default" type="button" onClick={this.filterSidebar}>
								<span className="glyphicon glyphicon-search"></span>
							</button>
						</span>
					</div>
					<div className="filter-list">
						<ul className="list-unstyled">{results}</ul>
					</div>
				</div>
			</div>
		);
	}
});
var SideBarListItem = React.createClass({
	replaceValue: function() {
		var breadcrumbData = {
			displayName: this.props.options.breadcrumbName + ": " + disp(this.props.data.name), 
			displayClass: this.props.options.breadcrumbClass, 
			filterType: this.props.options.filterType, 
			filterValue: this.refs.sideBarName.id
		};
		this.props.onSelectSidebarValue(breadcrumbData, true);
	}, 
	selectValue: function() {
		var breadcrumbData = {
			displayName: this.props.options.breadcrumbName + ": " + disp(this.props.data.name), 
			displayClass: this.props.options.breadcrumbClass, 
			filterType: this.props.options.filterType, 
			filterValue: this.refs.sideBarName.id
		};
		this.props.onSelectSidebarValue(breadcrumbData);
	},
	render: function() {
		var sd = this.props.data;
		if(sd.hide) { return null; }
		return (
			<li ref="sideBarName" id={sd.code} className="sidebarvalue">
				<a href="#" onClick={this.selectValue}><span className="glyphicon glyphicon-plus-sign"></span></a>
				&nbsp;
				<a href="#" onClick={this.replaceValue}>{disp(sd.name)} ({sd.count})</a>
			</li>);
	}
});

var SearchBar = React.createClass({
	checkKeypress: function(e) { if(e.keyCode == 13) { this.doSearch(); } },
	doSearch: function() { this.props.onSearch(this.refs.searchTextInput.value); },
	render: function() {
		return (
			<div id="topSearch" className="col-sm-12">
				<Breadcrumb onRemoveBreadcrumb={this.props.onRemoveBreadcrumb} breadcrumbs={this.props.breadcrumbs} />
				<div id="searchGroup" className="input-group input-group-sm">
					<input id="searchBox" type="text" className="form-control" ref="searchTextInput" onKeyDown={this.checkKeypress} />
					<span className="input-group-btn">
						<button id="searchBtn" className="btn btn-primary" type="button" onClick={this.doSearch}>
							<span className="glyphicon glyphicon-search"></span>
						</button>
					</span>
				</div>
			</div>
		);
	}
});
var Breadcrumb = React.createClass({
	render: function() {
		var id = 0, onRemoveBreadcrumb = this.props.onRemoveBreadcrumb;
		var results = this.props.breadcrumbs.map(function(v) { return (<BreadcrumbItem key={id++} onRemoveBreadcrumb={onRemoveBreadcrumb} data={v} />); });
		return (<div id="breadcrumbContainer">{results}</div>);
	}
});
var BreadcrumbItem = React.createClass({
	removeBreadcrumb: function() { this.props.onRemoveBreadcrumb(this.props.data.displayName); },
	render: function() {
		return (
			<span className="bc">
				<button id="searchBtn" className={"btn " + this.props.data.displayClass} type="button" onClick={this.removeBreadcrumb}>
					{this.props.data.displayName} <span className="glyphicon glyphicon-remove-sign"></span>
				</button>
			</span>
		);
	}
});

ReactDOM.render(<FullContainer options={options} />, document.getElementById("wholeContainer"));